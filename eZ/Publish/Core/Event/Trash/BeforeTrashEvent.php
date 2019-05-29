<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Trash;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeTrashEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.trash.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $location;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\TrashItem|null
     */
    private $result;

    /**
     * @var bool
     */
    private $resultSet = false;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getResult(): ?TrashItem
    {
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
