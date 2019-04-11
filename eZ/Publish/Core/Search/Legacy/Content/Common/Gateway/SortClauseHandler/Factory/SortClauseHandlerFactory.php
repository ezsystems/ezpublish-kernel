<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\Factory;

use Doctrine\DBAL\Connection;

class SortClauseHandlerFactory
{
    /**
     * @var iterable|\eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler[]
     */
    private $gateways = [];

    /**
     * @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler[]
     */
    private $gatewaysForDriver = [];

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    public function __construct(Connection $connection, iterable $gateways)
    {
        $this->connection = $connection;
        $this->gateways = $gateways;
    }

    public function getGateway(\eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause)
    {
        $driverName = $this->connection->getDatabasePlatform()->getName();

        // @todo prepare cached gatewaysForDriver

        foreach ($this->gatewaysForDriver[$driverName] as $sortClauseHandlerGateway) {
            /** @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler $sortClauseHandlerGateway */
            if ($sortClauseHandlerGateway->accept($sortClause)) {
                return $sortClauseHandlerGateway;
            }
        }
    }
}
