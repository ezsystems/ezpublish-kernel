<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ObjectState;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeUpdateObjectStateGroupEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.object_state_group.update.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    private $objectStateGroup;

    /**
     * @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct
     */
    private $objectStateGroupUpdateStruct;

    /**
     * @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup|null
     */
    private $updatedObjectStateGroup;

    public function __construct(ObjectStateGroup $objectStateGroup, ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct)
    {
        $this->objectStateGroup = $objectStateGroup;
        $this->objectStateGroupUpdateStruct = $objectStateGroupUpdateStruct;
    }

    public function getObjectStateGroup(): ObjectStateGroup
    {
        return $this->objectStateGroup;
    }

    public function getObjectStateGroupUpdateStruct(): ObjectStateGroupUpdateStruct
    {
        return $this->objectStateGroupUpdateStruct;
    }

    public function getUpdatedObjectStateGroup(): ?ObjectStateGroup
    {
        return $this->updatedObjectStateGroup;
    }

    public function setUpdatedObjectStateGroup(?ObjectStateGroup $updatedObjectStateGroup): void
    {
        $this->updatedObjectStateGroup = $updatedObjectStateGroup;
    }

    public function hasUpdatedObjectStateGroup(): bool
    {
        return $this->updatedObjectStateGroup instanceof ObjectStateGroup;
    }
}
