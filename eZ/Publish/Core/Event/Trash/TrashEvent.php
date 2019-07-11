<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Trash;

use eZ\Publish\API\Repository\Events\Trash\TrashEvent as TrashEventInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use Symfony\Contracts\EventDispatcher\Event;

final class TrashEvent extends Event implements TrashEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $location;

    /** @var \eZ\Publish\API\Repository\Values\Content\TrashItem|null */
    private $trashItem;

    public function __construct(
        ?TrashItem $trashItem,
        Location $location
    ) {
        $this->location = $location;
        $this->trashItem = $trashItem;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getTrashItem(): ?TrashItem
    {
        return $this->trashItem;
    }
}
