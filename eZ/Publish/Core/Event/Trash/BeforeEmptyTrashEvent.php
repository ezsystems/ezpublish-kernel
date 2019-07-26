<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Trash;

use eZ\Publish\API\Repository\Events\Trash\BeforeEmptyTrashEvent as BeforeEmptyTrashEventInterface;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeEmptyTrashEvent extends BeforeEvent implements BeforeEmptyTrashEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList|null */
    private $resultList;

    public function __construct()
    {
    }

    public function getResultList(): TrashItemDeleteResultList
    {
        if (!$this->hasResultList()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasResultList() or set it by setResultList() before you call getter.', TrashItemDeleteResultList::class));
        }

        return $this->resultList;
    }

    public function setResultList(?TrashItemDeleteResultList $resultList): void
    {
        $this->resultList = $resultList;
    }

    public function hasResultList(): bool
    {
        return $this->resultList instanceof TrashItemDeleteResultList;
    }
}
