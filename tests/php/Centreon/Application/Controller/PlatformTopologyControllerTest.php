<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

namespace Tests\Centreon\Application\Controller;

use Centreon\Domain\PlatformTopology\Interfaces\PlatformInterface;
use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use FOS\RestBundle\Context\Context;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Centreon\Domain\PlatformTopology\Model\PlatformRegistered;
use Centreon\Domain\PlatformTopology\Model\PlatformPending;
use Symfony\Component\HttpFoundation\Response;
use Centreon\Domain\PlatformTopology\Model\PlatformRelation;
use Centreon\Domain\PlatformTopology\Exception\PlatformTopologyException;
use Centreon\Infrastructure\PlatformTopology\Repository\Model\PlatformJsonGraph;
use Centreon\Domain\PlatformTopology\PlatformTopologyService;
use Centreon\Application\Controller\PlatformTopologyController;
use Centreon\Domain\PlatformTopology\Exception\PlatformTopologyConflictException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Centreon\Domain\PlatformTopology\Interfaces\PlatformTopologyServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PlatformTopologyControllerTest extends TestCase
{
    protected $goodJsonPlatform;
    protected $badJsonPlatform;

    /**
     * @var PlatformPending|null $platform
     */
    protected $platform;

    /**
     * @var PlatformRegistered
     */
    protected $centralPlatform;

    /**
     * @var PlatformPending
     */
    protected $pollerPlatform;

    /**
     * @var PlatformJsonGraph
     */
    protected $centralJsonGraphFormat;

    /**
     * @var PlatformJsonGraph
     */
    protected $pollerJsonGraphFormat;

    /**
     * @var PlatformTopologyService&MockObject $platformTopologyService
     */
    protected $platformTopologyService;

    protected $container;

    protected $request;

    protected function setUp(): void
    {
        $goodJsonPlatform = [
            'name' => 'poller1',
            'hostname' => 'localhost.localdomain',
            'address' => '1.1.1.2',
            'type' => 'poller',
            'parent_address' => '1.1.1.1'
        ];

        $this->goodJsonPlatform = json_encode($goodJsonPlatform);

        $this->platform = (new PlatformPending())
            ->setName($goodJsonPlatform['name'])
            ->setRelation('normal')
            ->setHostname($goodJsonPlatform['hostname'])
            ->setAddress($goodJsonPlatform['address'])
            ->setType($goodJsonPlatform['type'])
            ->setParentAddress($goodJsonPlatform['parent_address']);

        $this->centralPlatform = (new PlatformRegistered())
            ->setId(1)
            ->setName('Central')
            ->setHostname('localhost.localdomain')
            ->setType(PlatformRegistered::TYPE_CENTRAL)
            ->setAddress('192.168.1.1')
            ->setServerId(1)
            ->setRelation(PlatformRelation::NORMAL_RELATION);

        $this->pollerPlatform = (new PlatformPending())
            ->setId(2)
            ->setName('Poller')
            ->setHostname('poller.poller1')
            ->setType(PlatformRegistered::TYPE_POLLER)
            ->setAddress('192.168.1.2')
            ->setParentAddress('192.168.1.1')
            ->setParentId(1)
            ->setServerId(2)
            ->setRelation(PlatformRelation::NORMAL_RELATION);

        $this->centralJsonGraphFormat = new PlatformJsonGraph($this->centralPlatform);
        $this->pollerJsonGraphFormat = new PlatformJsonGraph($this->pollerPlatform);

        $this->badJsonPlatform = json_encode([
            'unknown_property' => 'unknown',
        ]);

        $this->platformTopologyService = $this->createMock(PlatformTopologyServiceInterface::class);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->willReturn(true);
        $token = $this->createMock(TokenInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->expects($this->any())
            ->method('has')
            ->willReturn(true);
        $this->container->expects($this->any())
            ->method('get')
            ->withConsecutive(
                [$this->equalTo('security.authorization_checker')],
                [$this->equalTo('parameter_bag')]
            )
            ->willReturnOnConsecutiveCalls(
                $authorizationChecker,
                new class () {
                    public function get(): string
                    {
                        return __DIR__ . '/../../../../../';
                    }
                }
            );

        $this->request = $this->createMock(Request::class);
    }

    /**
     * test addPlatformToTopology with bad json format
     */
    public function testAddPlatformToTopologyBadJsonFormat(): void
    {
        $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
        $platformTopologyController->setContainer($this->container);

        $this->request->expects($this->once())
            ->method('getContent')
            ->willReturn('[}');
        $this->expectException(PlatformTopologyException::class);
        $this->expectExceptionMessage('Error when decoding sent data');
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $platformTopologyController->addPlatformToTopology($this->request);
    }

    /**
     * test addPlatformToTopology with conflict
     * @throws PlatformTopologyConflictException
     */
    public function testAddPlatformToTopologyConflict(): void
    {
        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->goodJsonPlatform);

        $this->platformTopologyService->expects($this->any())
            ->method('addPlatformToTopology')
            ->will($this->throwException(new PlatformTopologyConflictException('conflict')));

        $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
        $platformTopologyController->setContainer($this->container);

        $view = $platformTopologyController->addPlatformToTopology($this->request);
        $this->assertEquals(
            $view,
            View::create(['message' => 'conflict'], Response::HTTP_CONFLICT)
        );
    }

    /**
     * test addPlatformToTopology with bad request
     * @throws PlatformTopologyException
     */
    public function testAddPlatformToTopologyBadRequest(): void
    {
        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->goodJsonPlatform);

        $this->platformTopologyService->expects($this->any())
            ->method('addPlatformToTopology')
            ->will($this->throwException(new PlatformTopologyException('bad request')));

        $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
        $platformTopologyController->setContainer($this->container);

        $view = $platformTopologyController->addPlatformToTopology($this->request);

        $this->assertEquals(
            $view,
            View::create(['message' => 'bad request'], Response::HTTP_BAD_REQUEST)
        );
    }

    /**
     * test addPlatformToTopology which succeed
     * @throws PlatformTopologyException
     */
    public function testAddPlatformToTopologySuccess(): void
    {
        $this->request->expects($this->any())
            ->method('getContent')
            ->willReturn($this->goodJsonPlatform);

        $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
        $platformTopologyController->setContainer($this->container);

        $view = $platformTopologyController->addPlatformToTopology($this->request);
        $this->assertEquals(
            $view,
            View::create(null, Response::HTTP_CREATED)
        );
    }

    public function testGetPlatformJsonGraph(): void
    {
        $completeTopology = [$this->centralPlatform, $this->pollerPlatform];
        $nodes[$this->centralJsonGraphFormat->getId()] = $this->centralJsonGraphFormat;
        $nodes[$this->pollerJsonGraphFormat->getId()] = $this->pollerJsonGraphFormat;

        $this->platformTopologyService->expects($this->once())
            ->method('getPlatformTopology')
            ->willReturn($completeTopology);

        $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
        $platformTopologyController->setContainer($this->container);

        $view = $platformTopologyController->getPlatformJsonGraph();

        $context = (new Context())->setGroups(PlatformTopologyController::SERIALIZER_GROUP_JSON_GRAPH);

        $this->assertEquals(
            $view,
            View::create(
                [
                    'graph' => [
                        'label' => 'centreon-topology',
                        'metadata' => [
                            'version' => '1.0.0'
                        ],
                        'nodes' => $nodes,
                        'edges' => [
                            [
                                "source" => "2",
                                "relation" => "normal",
                                "target" => "1"
                            ]
                        ]
                    ],
                ],
                Response::HTTP_OK
            )->setContext($context)
        );
    }

    public function testDeletePlatformTopologySuccess(): void
    {
        $platformTopologyController = new PlatformTopologyController($this->platformTopologyService);
        $platformTopologyController->setContainer($this->container);

        $view = $platformTopologyController->deletePlatform($this->pollerPlatform->getId());

        $this->assertEquals(
            $view,
            View::create(null, Response::HTTP_NO_CONTENT)
        );
    }
}
