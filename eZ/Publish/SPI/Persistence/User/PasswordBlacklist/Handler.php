<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\User\PasswordBlacklist;

interface Handler
{
    /**
     * Removes all entries from the password blacklist.
     */
    public function removeAll(): void;

    /**
     * Returns true if blacklist contains given password.
     *
     * @param string $password
     * @return bool
     */
    public function isBlacklisted(string $password): bool;

    /**
     * Add given passwords to the blacklist.
     *
     * @param iterable $passwords
     */
    public function insert(iterable $passwords): void;
}
