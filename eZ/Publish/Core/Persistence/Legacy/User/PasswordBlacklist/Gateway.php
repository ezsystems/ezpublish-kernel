<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist;

/**
 * Base class for password blacklist gateways.
 */
abstract class Gateway
{
    /**
     * Returns true if blacklist contains given password.
     *
     * @param string $password
     * @return bool
     */
    abstract public function isBlacklisted(string $password): bool;
}
