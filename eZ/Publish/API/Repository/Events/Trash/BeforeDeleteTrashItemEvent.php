<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Trash;

use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeDeleteTrashItemEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\TrashItem */
    private $trashItem;

    /** @var \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult|null */
    private $result;

    public function __construct(TrashItem $trashItem)
    {
        $this->trashItem = $trashItem;
    }

    public function getTrashItem(): TrashItem
    {
        return $this->trashItem;
    }

    public function getResult(): TrashItemDeleteResult
    {
        if (!$this->hasResult()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasResult() or set it by setResult() before you call getter.', TrashItemDeleteResult::class));
        }

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
