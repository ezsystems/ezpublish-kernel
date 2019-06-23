<?php

/**
 * File containing the Content Search Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Gateway;

use eZ\Publish\Core\Search\Legacy\Content\Gateway;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Doctrine\DBAL\DBALException;
use PDOException;
use RuntimeException;

/**
 * The Content Search Gateway provides the implementation for one database to
 * retrieve the desired content objects.
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

    /**
     * Returns a list of object satisfying the $criterion.
     *
     * @param Criterion $criterion
     * @param int $offset
     * @param int|null $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sort
     * @param array $languageFilter
     * @param bool $doCount
     *
     * @throws \RuntimeException
     *
     * @return mixed[][]
     */
    public function find(
        Criterion $criterion,
        $offset = 0,
        $limit = null,
        array $sort = null,
        array $languageFilter = [],
        $doCount = true
    ) {
        try {
            return $this->innerGateway->find($criterion, $offset, $limit, $sort, $languageFilter, $doCount);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }
}
