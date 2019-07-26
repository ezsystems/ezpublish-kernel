<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ObjectState;

use eZ\Publish\API\Repository\Events\ObjectState\CreateObjectStateGroupEvent as CreateObjectStateGroupEventInterface;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class CreateObjectStateGroupEvent extends AfterEvent implements CreateObjectStateGroupEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup */
    private $objectStateGroup;

    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct */
    private $objectStateGroupCreateStruct;

    public function __construct(
        ObjectStateGroup $objectStateGroup,
        ObjectStateGroupCreateStruct $objectStateGroupCreateStruct
    ) {
        $this->objectStateGroup = $objectStateGroup;
        $this->objectStateGroupCreateStruct = $objectStateGroupCreateStruct;
    }

    public function getObjectStateGroup(): ObjectStateGroup
    {
        return $this->objectStateGroup;
    }

    public function getObjectStateGroupCreateStruct(): ObjectStateGroupCreateStruct
    {
        return $this->objectStateGroupCreateStruct;
    }
}
