<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\Factory;

use Doctrine\DBAL\Connection;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\AbstractRandom;

class RandomSortClauseHandlerFactory
{
    /** @var iterable|\eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\AbstractRandom[] */
    private $randomSortClauseGateways = [];

    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    public function __construct(Connection $connection, iterable $randomSortClauseGateways)
    {
        $this->connection = $connection;
        $this->randomSortClauseGateways = $randomSortClauseGateways;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function getGateway(): AbstractRandom
    {
        $driverName = $this->connection->getDatabasePlatform()->getName();

        foreach ($this->randomSortClauseGateways as $gateway) {
            if ($gateway->getDriverName() === $driverName) {
                return $gateway;
            }
        }

        throw new InvalidArgumentException('$this->randomSortClauseGateways', 'No RandomSortClauseHandler found for driver ' . $driverName);
    }
}
