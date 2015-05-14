<?php
/**
 * File containing the DoctrineDatabase Location Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Legacy\Content\Location\Gateway;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter;
use eZ\Publish\Core\Search\Legacy\Content\Location\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use PDO;

/**
 * Location gateway implementation using the Doctrine database.
 */
class DoctrineDatabase extends Gateway
{
    /**
     * 2^30, since PHP_INT_MAX can cause overflows in DB systems, if PHP is run
     * on 64 bit systems
     */
    const MAX_LIMIT = 1073741824;

    /**
     * Database handler
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $handler;

    /**
     * @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter
     */
    private $criteriaConverter;

    /**
     * @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter
     */
    private $sortClauseConverter;

    /**
     * Construct from database handler
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $handler
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $criteriaConverter
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter $sortClauseConverter
     */
    public function __construct(
        DatabaseHandler $handler,
        CriteriaConverter $criteriaConverter,
        SortClauseConverter $sortClauseConverter
    )
    {
        $this->handler = $handler;
        $this->criteriaConverter = $criteriaConverter;
        $this->sortClauseConverter = $sortClauseConverter;
    }

    /**
     * Returns total count and data for all Locations satisfying the parameters.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param int $offset
     * @param int $limit
     * @param null|\eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     * @param bool $doCount
     *
     * @return mixed[][]
     */
    public function find( Criterion $criterion, $offset, $limit, array $sortClauses = null, $doCount = true )
    {
        $count = $doCount ? $this->getTotalCount( $criterion, $sortClauses ) : null;

        if ( !$doCount && $limit === 0 )
        {
            throw new \RuntimeException( "Invalid query, can not disable count and request 0 items at the same time" );
        }

        if ( $limit === 0 || ( $count !== null && $count <= $offset ) )
        {
            return array( "count" => $count, "rows" => array() );
        }

        $selectQuery = $this->handler->createSelectQuery();
        $selectQuery->select( 'ezcontentobject_tree.*' );

        if ( $sortClauses !== null )
        {
            $this->sortClauseConverter->applySelect( $selectQuery, $sortClauses );
        }

        $selectQuery
            ->from( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->innerJoin(
                'ezcontentobject',
                'ezcontentobject_tree.contentobject_id',
                'ezcontentobject.id'
            )
            ->innerJoin(
                'ezcontentobject_version',
                'ezcontentobject.id',
                'ezcontentobject_version.contentobject_id'
            );

        if ( $sortClauses !== null )
        {
            $this->sortClauseConverter->applyJoin( $selectQuery, $sortClauses );
        }

        $selectQuery->where(
            $this->criteriaConverter->convertCriteria( $selectQuery, $criterion ),
            $selectQuery->expr->eq(
                'ezcontentobject.status',
                //ContentInfo::STATUS_PUBLISHED
                $selectQuery->bindValue( 1, null, PDO::PARAM_INT )
            ),
            $selectQuery->expr->eq(
                'ezcontentobject_version.status',
                //VersionInfo::STATUS_PUBLISHED
                $selectQuery->bindValue( 1, null, PDO::PARAM_INT )
            ),
            $selectQuery->expr->neq(
                $this->handler->quoteColumn( "depth", "ezcontentobject_tree" ),
                $selectQuery->bindValue( 0, null, PDO::PARAM_INT )
            )
        );

        if ( $sortClauses !== null )
        {
            $this->sortClauseConverter->applyOrderBy( $selectQuery );
        }

        $selectQuery->limit( $limit, $offset );

        $statement = $selectQuery->prepare();
        $statement->execute();

        return array(
            "count" => $count,
            "rows" => $statement->fetchAll( PDO::FETCH_ASSOC )
        );
    }

    /**
     * Returns total results count for $criterion and $sortClauses
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param null|\eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     *
     * @return array
     */
    protected function getTotalCount( Criterion $criterion, $sortClauses )
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select( $query->alias( $query->expr->count( '*' ), 'count' ) )
            ->from( $this->handler->quoteTable( 'ezcontentobject_tree' ) )
            ->innerJoin(
                'ezcontentobject',
                'ezcontentobject_tree.contentobject_id',
                'ezcontentobject.id'
            )
            ->innerJoin(
                'ezcontentobject_version',
                'ezcontentobject.id',
                'ezcontentobject_version.contentobject_id'
            );

        if ( $sortClauses !== null )
        {
            $this->sortClauseConverter->applyJoin( $query, $sortClauses );
        }

        $query->where(
            $this->criteriaConverter->convertCriteria( $query, $criterion ),
            $query->expr->eq(
                'ezcontentobject.status',
                //ContentInfo::STATUS_PUBLISHED
                $query->bindValue( 1, null, PDO::PARAM_INT )
            ),
            $query->expr->eq(
                'ezcontentobject_version.status',
                //VersionInfo::STATUS_PUBLISHED
                $query->bindValue( 1, null, PDO::PARAM_INT )
            ),
            $query->expr->neq(
                $this->handler->quoteColumn( "depth", "ezcontentobject_tree" ),
                $query->bindValue( 0, null, PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $res = $statement->fetchAll( PDO::FETCH_ASSOC );
        return (int)$res[0]['count'];
    }
}
