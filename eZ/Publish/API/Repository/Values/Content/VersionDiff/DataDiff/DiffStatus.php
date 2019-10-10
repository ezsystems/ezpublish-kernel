<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\VersionDiff\DataDiff;

interface DiffStatus
{
    public const UNCHANGED = 'unchanged';

    public const ADDED = 'added';

    public const REMOVED = 'removed';
}
