<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Trash;

use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;
use eZ\Publish\Core\Event\AfterEvent;

final class EmptyTrashEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.trash.empty';

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList
     */
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
