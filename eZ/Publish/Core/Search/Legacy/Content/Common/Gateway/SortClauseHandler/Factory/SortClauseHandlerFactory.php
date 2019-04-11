<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\Factory;

use Doctrine\DBAL\Connection;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use ReflectionClass;

class SortClauseHandlerFactory
{
    /**
     * @var iterable|\eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler[]
     */
    private $gateways = [];

    /**
     * @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler[][]
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

    public function getGateway(string $sortClauseClass)
    {
        $gateways = $this->getGatewaysForDriver($this->connection->getDatabasePlatform()->getName());

        foreach ($gateways as $sortClauseHandlerGateway) {
            /** @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler $sortClauseHandlerGateway */
            $refClass = (new ReflectionClass($sortClauseClass))->newInstanceWithoutConstructor();

            if (!$refClass instanceof SortClause) {
                throw new InvalidArgumentException('$sortClauseClass', 'Must implement ' . SortClause::class);
            }
            if ($sortClauseHandlerGateway->supportedClass() === $sortClauseClass) {
                return $sortClauseHandlerGateway;
            }
//            if ($sortClauseHandlerGateway->accept($refClass)) {
//                return $sortClauseHandlerGateway;
//            }
        }

        throw new InvalidArgumentException('$sortClauseClass', 'No sort clause handler found for ' . $sortClauseClass);
    }

    private function getGatewaysForDriver(string $driverName): array
    {
        if (empty($this->gatewaysForDriver) && !empty($this->gateways)) {
            foreach ($this->gateways as $gateway) {
                $this->gatewaysForDriver[$gateway->getDriverName()][] = $gateway;
            }
        }

        if (!isset($this->gatewaysForDriver[$driverName])) {
            throw new InvalidArgumentException('$this->gatewaysForDriver', 'No gateways for driver ' . $driverName);
        }

        return $this->gatewaysForDriver[$driverName];
    }
}
