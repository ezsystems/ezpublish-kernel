<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ObjectState;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;

interface BeforeUpdateObjectStateGroupEvent extends BeforeEvent
{
    public function getObjectStateGroup(): ObjectStateGroup;

    public function getObjectStateGroupUpdateStruct(): ObjectStateGroupUpdateStruct;

    public function getUpdatedObjectStateGroup(): ObjectStateGroup;

    public function setUpdatedObjectStateGroup(?ObjectStateGroup $updatedObjectStateGroup): void;

    public function hasUpdatedObjectStateGroup(): bool;
}
