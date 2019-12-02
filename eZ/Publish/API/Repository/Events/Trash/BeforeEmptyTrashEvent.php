<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Trash;

use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeEmptyTrashEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList|null */
    private $resultList;

    public function __construct()
    {
    }

    public function getResultList(): TrashItemDeleteResultList
    {
        if (!$this->hasResultList()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not of type %s. Check hasResultList() or set it using setResultList() before you call the getter.', TrashItemDeleteResultList::class));
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
