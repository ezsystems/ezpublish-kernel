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
use UnexpectedValueException;

final class BeforeUpdateObjectStateGroupEvent extends BeforeEvent
{
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

    public function getUpdatedObjectStateGroup(): ObjectStateGroup
    {
        if (!$this->hasUpdatedObjectStateGroup()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasUpdatedObjectStateGroup() or set it by setUpdatedObjectStateGroup() before you call getter.', ObjectStateGroup::class));
        }

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
