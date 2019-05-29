<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Trash;

use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeDeleteTrashItemEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.trash.item_delete.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    private $trashItem;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult|null
     */
    private $result;

    public function __construct(TrashItem $trashItem)
    {
        $this->trashItem = $trashItem;
    }

    public function getTrashItem(): TrashItem
    {
        return $this->trashItem;
    }

    public function getResult(): ?TrashItemDeleteResult
    {
        return $this->result;
    }

    public function setResult(?TrashItemDeleteResult $result): void
    {
        $this->result = $result;
    }

    public function hasResult(): bool
    {
        return $this->result instanceof TrashItemDeleteResult;
    }
}
