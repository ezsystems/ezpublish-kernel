<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ObjectState;

use eZ\Publish\API\Repository\Events\ObjectState\BeforeCreateObjectStateGroupEvent as BeforeCreateObjectStateGroupEventInterface;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeCreateObjectStateGroupEvent extends BeforeEvent implements BeforeCreateObjectStateGroupEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct */
    private $objectStateGroupCreateStruct;

    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup|null */
    private $objectStateGroup;

    public function __construct(ObjectStateGroupCreateStruct $objectStateGroupCreateStruct)
    {
        $this->objectStateGroupCreateStruct = $objectStateGroupCreateStruct;
    }

    public function getObjectStateGroupCreateStruct(): ObjectStateGroupCreateStruct
    {
        return $this->objectStateGroupCreateStruct;
    }

    public function getObjectStateGroup(): ObjectStateGroup
    {
        if (!$this->hasObjectStateGroup()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasObjectStateGroup() or set it by setObjectStateGroup() before you call getter.', ObjectStateGroup::class));
        }

        return $this->objectStateGroup;
    }

    public function setObjectStateGroup(?ObjectStateGroup $objectStateGroup): void
    {
        $this->objectStateGroup = $objectStateGroup;
    }

    public function hasObjectStateGroup(): bool
    {
        return $this->objectStateGroup instanceof ObjectStateGroup;
    }
}
