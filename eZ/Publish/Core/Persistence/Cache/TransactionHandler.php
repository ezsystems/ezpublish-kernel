<?php

/**
 * File containing the Persistence Transaction Cache Handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\TransactionHandler as TransactionHandlerInterface;

/**
 * Persistence Transaction Cache Handler class.
 */
class TransactionHandler extends AbstractInMemoryPersistenceHandler implements TransactionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $this->cache->beginTransaction();

        $this->logger->logCall(__METHOD__);
        $this->persistenceHandler->transactionHandler()->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $this->logger->logCall(__METHOD__);
        $this->persistenceHandler->transactionHandler()->commit();

        $this->cache->commitTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        $this->logger->logCall(__METHOD__);
        $this->persistenceHandler->transactionHandler()->rollback();

        $this->cache->rollbackTransaction();
    }
}
