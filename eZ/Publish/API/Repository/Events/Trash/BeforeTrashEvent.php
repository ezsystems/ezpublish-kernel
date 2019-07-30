<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Trash;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeTrashEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location;

    /** @var \eZ\Publish\API\Repository\Values\Content\TrashItem|null */
    private $result;

    /** @var bool */
    private $resultSet = false;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getResult(): TrashItem
    {
        if (!$this->isResultSet()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s (or null). Check isResultSet() or set it by setResult() before you call getter.', TrashItem::class));
        }

        return $this->result;
    }

    public function setResult(?TrashItem $result): void
    {
        $this->result = $result;
        $this->resultSet = true;
    }

    public function hasTrashItem(): bool
    {
        return $this->result instanceof TrashItem;
    }

    public function resetResult(): void
    {
        $this->result = null;
        $this->resultSet = false;
    }

    public function isResultSet(): bool
    {
        return $this->resultSet;
    }
}
