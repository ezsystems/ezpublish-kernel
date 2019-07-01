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
use eZ\Publish\Core\Event\AfterEvent;

final class CreateObjectStateEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectState */
    private $objectState;

    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup */
    private $objectStateGroup;

    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct */
    private $objectStateCreateStruct;

    public function __construct(
        ObjectState $objectState,
        ObjectStateGroup $objectStateGroup,
        ObjectStateCreateStruct $objectStateCreateStruct
    ) {
        $this->objectState = $objectState;
        $this->objectStateGroup = $objectStateGroup;
        $this->objectStateCreateStruct = $objectStateCreateStruct;
    }

    public function getObjectState(): ObjectState
    {
        return $this->objectState;
    }

    public function getObjectStateGroup(): ObjectStateGroup
    {
        return $this->objectStateGroup;
    }

    public function getObjectStateCreateStruct(): ObjectStateCreateStruct
    {
        return $this->objectStateCreateStruct;
    }
}
