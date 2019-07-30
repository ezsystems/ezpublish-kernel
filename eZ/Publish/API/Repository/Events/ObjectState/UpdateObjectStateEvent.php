<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ObjectState;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class UpdateObjectStateEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectState */
    private $updatedObjectState;

    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectState */
    private $objectState;

    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct */
    private $objectStateUpdateStruct;

    public function __construct(
        ObjectState $updatedObjectState,
        ObjectState $objectState,
        ObjectStateUpdateStruct $objectStateUpdateStruct
    ) {
        $this->updatedObjectState = $updatedObjectState;
        $this->objectState = $objectState;
        $this->objectStateUpdateStruct = $objectStateUpdateStruct;
    }

    public function getUpdatedObjectState(): ObjectState
    {
        return $this->updatedObjectState;
    }

    public function getObjectState(): ObjectState
    {
        return $this->objectState;
    }

    public function getObjectStateUpdateStruct(): ObjectStateUpdateStruct
    {
        return $this->objectStateUpdateStruct;
    }
}
