<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Trash;

use eZ\Publish\API\Repository\Events\Trash\EmptyTrashEvent as EmptyTrashEventInterface;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class EmptyTrashEvent extends AfterEvent implements EmptyTrashEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList */
    private $resultList;

    public function __construct(TrashItemDeleteResultList $resultList)
    {
        $this->resultList = $resultList;
    }

    public function getResultList(): TrashItemDeleteResultList
    {
        return $this->resultList;
    }
}
