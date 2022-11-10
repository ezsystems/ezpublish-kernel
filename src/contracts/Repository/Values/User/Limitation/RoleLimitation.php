<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Core\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation;

final class RoleLimitation extends Limitation
{
    public const IDENTIFIER = 'Role';

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }
}
