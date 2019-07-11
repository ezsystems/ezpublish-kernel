<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ObjectState;

use eZ\Publish\API\Repository\Events\BeforeEvent;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;

interface BeforeCreateObjectStateEvent extends BeforeEvent
{
    public function getObjectStateGroup(): ObjectStateGroup;

    public function getObjectStateCreateStruct(): ObjectStateCreateStruct;

    public function getObjectState(): ObjectState;

    public function setObjectState(?ObjectState $objectState): void;

    public function hasObjectState(): bool;
}
