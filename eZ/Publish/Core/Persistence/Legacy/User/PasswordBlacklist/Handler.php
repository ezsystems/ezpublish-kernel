<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist;

use eZ\Publish\SPI\Persistence\User\PasswordBlacklist\Handler as HandlerInterface;

/**
 * Storage Engine handler for password blacklist.
 */
class Handler implements HandlerInterface
{
    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\User\PasswordBlackList\Gateway
     */
    private $gateway;

    /**
     * @param \eZ\Publish\Core\Persistence\Legacy\User\PasswordBlacklist\Gateway $gateway
     */
    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(): void
    {
        $this->gateway->removeAll();
    }

    /**
     * {@inheritdoc}
     */
    public function isBlacklisted(string $password): bool
    {
        return $this->gateway->isBlacklisted($password);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(iterable $passwords): void
    {
        $this->gateway->insert($passwords);
    }
}
