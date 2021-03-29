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
declare(strict_types=1);

namespace Centreon\Domain\Monitoring\MetaService\UseCase\V21\MetaServiceMetric;

use Centreon\Domain\Monitoring\MetaService\Model\MetaServiceMetric;

/**
 * This class is a DTO for the FindMetaServiceMetrics use case.
 *
 * @package Centreon\Domain\Monitoring\MetaService\UseCase\V21\MetaServiceMetric
 */
class FindMetaServiceMetricsResponse
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private $metaServiceMetrics = [];

    /**
     * @param MetaServiceMetric[] $metaServiceConfigurations
     */
    public function setMetaServiceMetrics(array $metaServiceMetrics): void
    {
        foreach ($metaServiceMetrics as $metaServiceMetric) {
            $this->metaServiceMetrics[] = [
                'id' => $metaServiceMetric->getId(),
                'name' => $metaServiceMetric->getName(),
                'unit' => $metaServiceMetric->getUnit(),
                'current_value' => $metaServiceMetric->getCurrentValue(),
                'resource' => $metaServiceMetric->getResource()
            ];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMetaServiceMetrics(): array
    {
        return $this->metaServiceMetrics;
    }
}
