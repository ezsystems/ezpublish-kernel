<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ObjectState;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\Core\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeCreateObjectStateEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup */
    private $objectStateGroup;

    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct */
    private $objectStateCreateStruct;

    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectState|null */
    private $objectState;

    public function __construct(ObjectStateGroup $objectStateGroup, ObjectStateCreateStruct $objectStateCreateStruct)
    {
        $this->objectStateGroup = $objectStateGroup;
        $this->objectStateCreateStruct = $objectStateCreateStruct;
    }

    public function getObjectStateGroup(): ObjectStateGroup
    {
        return $this->objectStateGroup;
    }

    public function getObjectStateCreateStruct(): ObjectStateCreateStruct
    {
        return $this->objectStateCreateStruct;
    }

    public function getObjectState(): ObjectState
    {
        if (!$this->hasObjectState()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasObjectState() or set it by setObjectState() before you call getter.', ObjectState::class));
        }

        return $this->objectState;
    }

    public function setObjectState(?ObjectState $objectState): void
    {
        $this->objectState = $objectState;
    }

    public function hasObjectState(): bool
    {
        return $this->objectState instanceof ObjectState;
    }
}
