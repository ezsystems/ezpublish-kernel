<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Trash;

use eZ\Publish\API\Repository\Events\BeforeEvent;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;

interface BeforeEmptyTrashEvent extends BeforeEvent
{
    public function getResultList(): TrashItemDeleteResultList;

    public function setResultList(?TrashItemDeleteResultList $resultList): void;

    public function hasResultList(): bool;
}