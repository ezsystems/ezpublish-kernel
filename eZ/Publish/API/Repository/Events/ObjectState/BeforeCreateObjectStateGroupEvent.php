<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ObjectState;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;

interface BeforeCreateObjectStateGroupEvent
{
    public function getObjectStateGroupCreateStruct(): ObjectStateGroupCreateStruct;

    public function getObjectStateGroup(): ObjectStateGroup;

    public function setObjectStateGroup(?ObjectStateGroup $objectStateGroup): void;

    public function hasObjectStateGroup(): bool;
}
