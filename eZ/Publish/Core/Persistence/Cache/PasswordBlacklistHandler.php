<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\User\PasswordBlacklist\Handler as PasswordBlacklistHandlerInterface;

class PasswordBlacklistHandler extends AbstractHandler implements PasswordBlacklistHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function removeAll(): void
    {
        $this->logger->logCall(__METHOD__);

        $this->persistenceHandler->passwordBlacklistHandler()->removeAll();
    }

    /**
     * {@inheritdoc}
     */
    public function isBlacklisted(string $password): bool
    {
        $this->logger->logCall(__METHOD__, [
            'password' => $password,
        ]);

        return $this->persistenceHandler->passwordBlacklistHandler()->isBlacklisted($password);
    }

    /**
     * {@inheritdoc}
     */
    public function insert(iterable $passwords): void
    {
        $this->logger->logCall(__METHOD__);

        $this->persistenceHandler->passwordBlacklistHandler()->insert($passwords);
    }
}
