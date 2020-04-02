<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Location\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Search\Legacy\Content\Location\Gateway;
use Doctrine\DBAL\DBALException;
use PDOException;

/**
 * Base class for location gateways.
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway.
     *
     * @param Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function find(
        Criterion $criterion,
        $offset = 0,
        $limit = null,
        array $sortClauses = null,
        array $languageFilter = [],
        $doCount = true
    ): array {
        try {
            return $this->innerGateway->find($criterion, $offset, $limit, $sortClauses, $languageFilter, $doCount);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
