<?php
/**
 * File containing the DoctrineDatabase Location Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Legacy\Content\Location\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway as ContentTypeGateway;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter;
use eZ\Publish\Core\Search\Legacy\Content\Location\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\MapLocationDistance;
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
     * @var \eZ\Publish\Core\Search\Legacy\Content\Type\Gateway
     */
    protected $contentTypeGateway;

    /**
     * Construct from database handler
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $handler
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $criteriaConverter
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter $sortClauseConverter
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway $contentTypeGateway
     */
    public function __construct(
        DatabaseHandler $handler,
        CriteriaConverter $criteriaConverter,
        SortClauseConverter $sortClauseConverter,
        ContentTypeGateway $contentTypeGateway
    )
    {
        $this->handler = $handler;
        $this->criteriaConverter = $criteriaConverter;
        $this->sortClauseConverter = $sortClauseConverter;
        $this->contentTypeGateway = $contentTypeGateway;
    }

    /**
     * Returns total count and data for all Locations satisfying the parameters.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param int $offset
     * @param int|null $limit
     * @param null|\eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     *
     * @return mixed[][]
     */
    public function find( Criterion $criterion, $offset = 0, $limit = null, array $sortClauses = null )
    {
        $fieldMap = $this->getFieldMap( $sortClauses );
        $count = $this->getTotalCount( $criterion, $sortClauses, $fieldMap );
        if ( $limit === 0 )
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
            $this->sortClauseConverter->applyJoin( $selectQuery, $sortClauses, $fieldMap );
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

        $selectQuery->limit(
            $limit > 0 ? $limit : self::MAX_LIMIT,
            $offset
        );

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
     * @param array $fieldMap
     *
     * @return array
     */
    protected function getTotalCount( Criterion $criterion, $sortClauses, array $fieldMap )
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
            $this->sortClauseConverter->applyJoin( $query, $sortClauses, $fieldMap );
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

    /**
     * Returns the field map if given $sortClauses contain a Field sort clause.
     *
     * Otherwise an empty array is returned.
     *
     * @param null|\eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sortClauses
     *
     * @return array
     */
    protected function getFieldMap( $sortClauses )
    {
        foreach ( (array)$sortClauses as $sortClause )
        {
            if ( $sortClause instanceof Field || $sortClause instanceof MapLocationDistance )
            {
                return $this->contentTypeGateway->getFieldMap();
            }
        }

        return array();
    }
}
