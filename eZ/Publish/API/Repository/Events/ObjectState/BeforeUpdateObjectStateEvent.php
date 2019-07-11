<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ObjectState;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;

interface BeforeUpdateObjectStateEvent extends BeforeEvent
{
    public function getObjectState(): ObjectState;

    public function getObjectStateUpdateStruct(): ObjectStateUpdateStruct;

    public function getUpdatedObjectState(): ObjectState;

    public function setUpdatedObjectState(?ObjectState $updatedObjectState): void;

    public function hasUpdatedObjectState(): bool;
}
