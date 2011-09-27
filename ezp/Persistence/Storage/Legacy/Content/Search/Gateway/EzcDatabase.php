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
    ezp\Persistence\Content\Query\Criterion;

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
    protected $criteriaConverter;

    /**
     * Sort clause converter
     *
     * @var SortClauseConverter
     */
    protected $sortClauseConverter;

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
        CriteriaConverter $criteriaConverter,
        SortClauseConverter $sortClauseConverter,
        QueryBuilder $queryBuilder
    )
    {
        $this->handler             = $handler;
        $this->criteriaConverter   = $criteriaConverter;
        $this->sortClauseConverter = $sortClauseConverter;
        $this->queryBuilder        = $queryBuilder;
    }

    /**
     * Returns a list of object satisfying the $criterion.
     *
     * @param Criterion $criterion
     * @param int $offset
     * @param int|null $limit
     * @param \ezp\Persistence\Content\Query\SortClause[] $sort
     * @param string[] $translations
     * @return mixed[][]
     */
    public function find( Criterion $criterion, $offset = 0, $limit = null, array $sort = null, array $translations = null )
    {
        $limit = $limit !== null ? $limit : PHP_INT_MAX;

        // Get full object count
        $query = $this->handler->createSelectQuery();
        $condition = $this->getQueryCondition( $criterion, $query, $translations );

        $query
            ->select( 'COUNT( * )' )
            ->from( $this->handler->quoteTable( 'ezcontentobject' ) )
            ->where( $condition );

        $statement = $query->prepare();
        $statement->execute();

        $count = (int)$statement->fetchColumn();

        if ( $count === 0 || $limit === 0 )
        {
            return array( 'count' => $count, 'rows' => array() );
        }

        $contentIds = $this->getContentIds( $query, $condition, $offset, $limit );

        return array(
            'count' => $count,
            'rows'  => $this->loadContent( $contentIds, $translations ),
        );
    }

    /**
     * Get query condition
     *
     * @param Criterion $criterion
     * @param \ezcQuerySelect $query
     * @param mixed $translations
     * @return string
     */
    protected function getQueryCondition( Criterion $criterion, \ezcQuerySelect $query, $translations )
    {
        $condition = $this->criteriaConverter->convertCriteria( $query, $criterion );

        if ( $translations === null )
        {
            return $condition;
        }

        $translationQuery = $query->subSelect();
        $translationQuery->select(
            $this->handler->quoteColumn( 'contentobject_id' )
        )->from(
            $this->handler->quoteTable( 'ezcontentobject_attribute' )
        )->where(
            $translationQuery->expr->in(
                $this->handler->quoteColumn( 'language_code' ),
                $translations
            )
        );

        return $query->expr->lAnd(
            $condition,
            $query->expr->in(
                $this->handler->quoteColumn( 'id' ),
                $translationQuery
            )
        );
    }

    /**
     * Get sorted arrays of content IDs, which should be returned
     *
     * @param \ezcQuerySelect ixed $query
     * @param string $condition
     * @param int $offset
     * @param int $limit
     * @return int[]
     */
    protected function getContentIds( \ezcQuerySelect $query, $condition, $offset, $limit )
    {
        $query->reset();
        $query->select(
            $this->handler->quoteColumn( 'id', 'ezcontentobject' )
        )->from(
            $this->handler->quoteTable( 'ezcontentobject' )
        )->where( $condition )->limit( $limit, $offset );

        // @TODO: Apply sorting

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_COLUMN );
    }

    /**
     * Load the actual content based on the provided IDs
     *
     * @param array $contentIds
     * @param mixed $translations
     * @return mixed[]
     */
    protected function loadContent( array $contentIds, $translations )
    {
        $loadQuery = $this->queryBuilder->createFindQuery( $translations );
        $loadQuery->where(
            $loadQuery->expr->in(
                $this->handler->quoteColumn( 'id', 'ezcontentobject' ),
                $contentIds
            )
        );

        $statement = $loadQuery->prepare();
        $statement->execute();

        // @TODO: Ensure the sorting of the rows repects the sorting of the
        // contentIds array?

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }
}

