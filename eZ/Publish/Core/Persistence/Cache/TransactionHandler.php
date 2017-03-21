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
class TransactionHandler extends AbstractHandler implements TransactionHandlerInterface
{
    /**
     * @todo Maybe this can be solved by contributing to Symfony, as in for instance using a layered cache with memory
     * cache first and use saveDefered so cache is not persisted before commit is made, and ommited on rollback.
     *
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
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
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
        $this->logger->logCall(__METHOD__);
        $this->cache->clear(); // TIMBER!! @see beginTransaction()
        $this->persistenceHandler->transactionHandler()->rollback();
    }
}
