<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Trash;

use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeEmptyTrashEvent extends BeforeEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList|null
     */
    private $resultList;

    public function __construct()
    {
    }

    public function getResultList(): ?TrashItemDeleteResultList
    {
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
