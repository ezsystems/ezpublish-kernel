<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ObjectState;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use eZ\Publish\Core\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeUpdateObjectStateEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectState */
    private $objectState;

    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct */
    private $objectStateUpdateStruct;

    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectState|null */
    private $updatedObjectState;

    public function __construct(ObjectState $objectState, ObjectStateUpdateStruct $objectStateUpdateStruct)
    {
        $this->objectState = $objectState;
        $this->objectStateUpdateStruct = $objectStateUpdateStruct;
    }

    public function getObjectState(): ObjectState
    {
        return $this->objectState;
    }

    public function getObjectStateUpdateStruct(): ObjectStateUpdateStruct
    {
        return $this->objectStateUpdateStruct;
    }

    public function getUpdatedObjectState(): ObjectState
    {
        if (!$this->hasUpdatedObjectState()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasUpdatedObjectState() or set it by setUpdatedObjectState() before you call getter.', ObjectState::class));
        }

        return $this->updatedObjectState;
    }

    public function setUpdatedObjectState(?ObjectState $updatedObjectState): void
    {
        $this->updatedObjectState = $updatedObjectState;
    }

    public function hasUpdatedObjectState(): bool
    {
        return $this->updatedObjectState instanceof ObjectState;
    }
}
