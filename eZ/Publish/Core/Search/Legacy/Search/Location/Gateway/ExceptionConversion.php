<?php
/**
 * File containing the Location Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Location\Gateway;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Location\Gateway;
use Doctrine\DBAL\DBALException;
use PDOException;
use RuntimeException;

/**
 * Base class for location gateways.
 */
class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway
     *
     * @var Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway
     *
     * @param Gateway $innerGateway
     */
    public function __construct( Gateway $innerGateway )
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * Returns total count and data for all Locations satisfying the parameters.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param int $offset
     * @param int|null $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     *
     * @return mixed[][]
     */
    public function find( Criterion $criterion, $offset = 0, $limit = null, array $sortClauses = null )
    {
        try
        {
            return $this->innerGateway->find( $criterion, $offset, $limit, $sortClauses );
        }
        catch ( DBALException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
        catch ( PDOException $e )
        {
            throw new RuntimeException( 'Database error', 0, $e );
        }
    }
}
