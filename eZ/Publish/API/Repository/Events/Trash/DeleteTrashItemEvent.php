<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Trash;

use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class DeleteTrashItemEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\TrashItem */
    private $trashItem;

    /** @var \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult */
    private $result;

    public function __construct(
        TrashItemDeleteResult $result,
        TrashItem $trashItem
    ) {
        $this->trashItem = $trashItem;
        $this->result = $result;
    }

    public function getTrashItem(): TrashItem
    {
        return $this->trashItem;
    }

    public function getResult(): TrashItemDeleteResult
    {
        return $this->result;
    }
}
