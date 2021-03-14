<?php

use Centreon\Domain\Monitoring\CommandLineTrait;
use Centreon\Domain\HostConfiguration\HostMacro;
use Centreon\Domain\ServiceConfiguration\ServiceMacro;

uses(CommandLineTrait::class);

function generateMacroEntities (array $macros): array
{
    $generatedMacros = [];
    foreach ($macros as $macro) {
        $generatedMacros[] = (new $macro[0]())
            ->setName($macro[1])
            ->setValue($macro[2])
            ->setPassword($macro[3]);
    }
    return $generatedMacros;
};

function replaceMacroValuesInConfigurationCommand (string $configurationCommand, array $macros): string
{
    foreach ($macros as $macro) {
        $configurationCommand = str_replace($macro->getName(), $macro->getValue(), $configurationCommand);
    }
    $configurationCommand = str_replace('$USER1$', '/centreon/plugins', $configurationCommand);
    return $configurationCommand;
};

it('hides password in service command line', function (string $encryptedMonitoringCommand, array $macros) {
    $macros = generateMacroEntities($macros);
    $configurationCommand = '$USER1$/plugin.pl --a="$_HOSTWITHOUTSPACE$" $_HOSTWITHSPACE$ '
        . '-b "$_SERVICEWITHOUTSPACE$" $_SERVICEWITHSPACE$ $_SERVICEEXTRAOPTIONS$';
    $monitoringCommand = replaceMacroValuesInConfigurationCommand($configurationCommand, $macros);

    $result = $this->buildCommandLineFromConfiguration(
        $configurationCommand,
        $monitoringCommand,
        $macros,
        '****'
    );

    expect($result)->toBe($encryptedMonitoringCommand);
})->with([
    [
        '/centreon/plugins/plugin.pl --a="****" **** -b "****" **** extra options',
        [
            [HostMacro::class, '$_HOSTWITHOUTSPACE$', 'withoutSpace', true],
            [HostMacro::class, '$_HOSTWITHSPACE$', 'with space', true],
            [ServiceMacro::class, '$_SERVICEWITHOUTSPACE$', 'withoutSpace', true],
            [ServiceMacro::class, '$_SERVICEWITHSPACE$', 'with space', true],
            [ServiceMacro::class, '$_SERVICEEXTRAOPTIONS$', 'extra options', false],
        ],
    ],
    [
        '/centreon/plugins/plugin.pl --a="withoutSpace" **** -b "withoutSpace" **** extra options',
        [
            [HostMacro::class, '$_HOSTWITHOUTSPACE$', 'withoutSpace', false],
            [HostMacro::class, '$_HOSTWITHSPACE$', 'with space', true],
            [ServiceMacro::class, '$_SERVICEWITHOUTSPACE$', 'withoutSpace', false],
            [ServiceMacro::class, '$_SERVICEWITHSPACE$', 'with space', true],
            [ServiceMacro::class, '$_SERVICEEXTRAOPTIONS$', 'extra options', false],
        ],
    ],
]);
