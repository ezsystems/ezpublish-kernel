<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Trash;

use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult;
use eZ\Publish\API\Repository\Values\Content\TrashItem;

interface BeforeDeleteTrashItemEvent
{
    public function getTrashItem(): TrashItem;

    public function getResult(): TrashItemDeleteResult;

    public function setResult(?TrashItemDeleteResult $result): void;

    public function hasResult(): bool;
}
