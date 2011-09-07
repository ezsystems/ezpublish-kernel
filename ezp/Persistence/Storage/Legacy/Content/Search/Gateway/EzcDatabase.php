<?php
/**
 * File containing the EzcDatabase content locator gateway class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Search\Gateway;
use ezp\Persistence\Storage\Legacy\Content\Search\Gateway,
    ezp\Persistence\Storage\Legacy\EzcDbHandler,
    ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase\QueryBuilder,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Search,
    ezp\Persistence\Content\Criterion;

/**
 * Content locator gateway implementation using the zeta handler component.
 */
class EzcDatabase extends Gateway
{
    /**
     * Database handler
     *
     * @var EzcDbHandler
     */
    protected $handler;

    /**
     * Criteria converter
     *
     * @var CriteriaConverter
     */
    protected $converter;

    /**
     * Content load query builder
     *
     * @var ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Construct from handler handler
     *
     * @param EzcDbHandler $handler
     * @return void
     */
    public function __construct(
        EzcDbHandler $handler,
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder )
    {
        $this->handler = $handler;
        $this->converter = $converter;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Returns a list of object satisfying the $criterion.
     *
     * @param Criterion $criterion
     * @param int $offset
     * @param int|null $limit
     * @param \ezp\Persistence\Content\Query\SortClause[] $sort
     * @return mixed[][]
     * @TODO This method now uses 3 querys (counting, ID fetching, loading) to
     *       enable proper use of $offset and $limit. Do we find a way to
     *       reduce this query count?
     */
    public function find( Criterion $criterion, $offset = 0, $limit = null, array $sort = null )
    {
        // Get full object count
        $query = $this->handler->createSelectQuery();
        $condition = $this->converter->convertCriteria( $query, $criterion );

        $query
            ->select( 'COUNT( * )' )
            ->from( $this->handler->quoteTable( 'ezcontentobject' ) )
            ->where( $condition );
        $statement = $query->prepare();
        $statement->execute();

        $count = (int)$statement->fetchColumn();

        if ( $count === 0 )
        {
            return array( 'count' => 0, 'rows' => array() );
        }

        // Fetch IDs of resulting content objects
        // This is neccessary to be able to use $offset and $limit properly
        $query->reset();
        $query->select(
            $this->handler->quoteColumn( 'id', 'ezcontentobject' )
        )->from(
            $this->handler->quoteTable( 'ezcontentobject' )
        )->where( $condition )->limit( $limit, $offset );

        $statement = $query->prepare();
        $statement->execute();

        $contentIds = $statement->fetchAll( \PDO::FETCH_COLUMN );

        // Load content itself
        $loadQuery = $this->queryBuilder->createFindQuery();
        $loadQuery->where(
            $loadQuery->expr->in(
                $this->handler->quoteColumn( 'id', 'ezcontentobject' ),
                $contentIds
            )
        );

        $statement = $loadQuery->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );

        return array(
            'count' => $count,
            'rows' => $rows,
        );
    }
}

