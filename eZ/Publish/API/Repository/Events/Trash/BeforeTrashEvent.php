<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Trash;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\TrashItem;

interface BeforeTrashEvent
{
    public function getLocation(): Location;

    public function getResult(): TrashItem;

    public function setResult(?TrashItem $result): void;

    public function hasTrashItem(): bool;

    public function resetResult(): void;

    public function isResultSet(): bool;
}
