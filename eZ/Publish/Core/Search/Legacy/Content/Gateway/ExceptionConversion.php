<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Gateway;

use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Search\Legacy\Content\Gateway;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Doctrine\DBAL\DBALException;
use PDOException;

/**
 * The Content Search Gateway provides the implementation for one database to
 * retrieve the desired content objects.
 */
class ExceptionConversion extends Gateway
{
    /**
     * @var \eZ\Publish\Core\Search\Legacy\Content\Gateway
     */
    protected $innerGateway;

    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function find(
        Criterion $criterion,
        $offset = 0,
        $limit = null,
        array $sort = null,
        array $languageFilter = [],
        $doCount = true
    ): array {
        try {
            return $this->innerGateway->find($criterion, $offset, $limit, $sort, $languageFilter, $doCount);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
