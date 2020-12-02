<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Application\Controller;

use Centreon\Domain\HostConfiguration\UseCase\V21\FindHostGroups;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Infrastructure\HostConfiguration\API\Model\HostGroupV21;
use FOS\RestBundle\View\View;

/**
 * This class is designed to provide APIs for the context of host group.
 *
 * @package Centreon\Application\Controller
 */
class HostGroupController extends AbstractController
{
    /**
     * @param RequestParametersInterface $requestParameters
     * @param FindHostGroups $findHostGroup
     * @return View
     * @throws \Exception
     */
    public function findHostGroups(
        RequestParametersInterface $requestParameters,
        FindHostGroups $findHostGroup
    ): View {
        $this->denyAccessUnlessGrantedForApiConfiguration();
        $response = $findHostGroup->execute();
        return $this->view(
            [
                'result' => HostGroupV21::createFromResponse($response),
                'meta' => $requestParameters->toArray()
            ]
        );
    }
}
