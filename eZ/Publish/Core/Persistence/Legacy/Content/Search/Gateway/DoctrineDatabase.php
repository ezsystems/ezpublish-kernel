<?php
/**
 * File containing the DoctrineDatabase Content search Gateway class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\SortClauseConverter;
use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * Content locator gateway implementation using the Doctrine database.
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
     * Criteria converter
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter
     */
    protected $criteriaConverter;

    /**
     * Sort clause converter
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\SortClauseConverter
     */
    protected $sortClauseConverter;

    /**
     * Content load query builder
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Caching language handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
     */
    protected $languageHandler;

    /**
     * Language mask generator
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Construct from handler handler
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $handler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\CriteriaConverter $criteriaConverter
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Search\Common\Gateway\SortClauseConverter $sortClauseConverter
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase\QueryBuilder $queryBuilder
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator
     */
    public function __construct(
        DatabaseHandler $handler,
        CriteriaConverter $criteriaConverter,
        SortClauseConverter $sortClauseConverter,
        QueryBuilder $queryBuilder,
        LanguageHandler $languageHandler,
        LanguageMaskGenerator $languageMaskGenerator
    )
    {
        $this->handler = $handler;
        $this->criteriaConverter = $criteriaConverter;
        $this->sortClauseConverter = $sortClauseConverter;
        $this->queryBuilder = $queryBuilder;
        $this->languageHandler = $languageHandler;
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    /**
     * Returns a list of object satisfying the $filter.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if Criterion is not applicable to its target
     *
     * @param Criterion $filter
     * @param int $offset
     * @param int|null $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sort
     * @param string[] $translations
     *
     * @return mixed[][]
     */
    public function find( Criterion $filter, $offset = 0, $limit = null, array $sort = null, array $translations = null )
    {
        $limit = $limit !== null ? $limit : self::MAX_LIMIT;

        $count = $this->getResultCount( $filter, $sort, $translations );
        if ( $limit === 0 || $count <= $offset )
        {
            return array( 'count' => $count, 'rows' => array() );
        }

        $contentInfoList = $this->getContentInfoList( $filter, $sort, $offset, $limit, $translations );

        return array(
            'count' => $count,
            'rows' => $contentInfoList,
        );
    }

    /**
     * Get query condition
     *
     * @param Criterion $filter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param mixed $translations
     *
     * @return string
     */
    protected function getQueryCondition( Criterion $filter, SelectQuery $query, $translations )
    {
        $condition = $query->expr->lAnd(
            $this->criteriaConverter->convertCriteria( $query, $filter ),
            $query->expr->eq(
                'ezcontentobject.status',
                ContentInfo::STATUS_PUBLISHED
            ),
            $query->expr->eq(
                'ezcontentobject_version.status',
                VersionInfo::STATUS_PUBLISHED
            )
        );

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
                $this->handler->quoteColumn( 'id', 'ezcontentobject' ),
                $translationQuery
            )
        );
    }

    /**
     * Get result count
     *
     * @param Criterion $filter
     * @param array $sort
     * @param mixed $translations
     * @return int
     */
    protected function getResultCount( Criterion $filter, $sort, $translations )
    {
        $query = $this->handler->createSelectQuery();

        $columnName = $this->handler->quoteColumn( 'id', 'ezcontentobject' );
        $query
            ->select( "COUNT( DISTINCT $columnName )" )
            ->from( $this->handler->quoteTable( 'ezcontentobject' ) )
            ->innerJoin(
                'ezcontentobject_version',
                'ezcontentobject.id',
                'ezcontentobject_version.contentobject_id'
            );

        if ( $sort !== null )
        {
            $this->sortClauseConverter->applyJoin( $query, $sort );
        }

        $query->where(
            $this->getQueryCondition( $filter, $query, $translations )
        );

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Get sorted arrays of content IDs, which should be returned
     *
     * @param Criterion $filter
     * @param array $sort
     * @param mixed $offset
     * @param mixed $limit
     * @param mixed $translations
     *
     * @return int[]
     */
    protected function getContentInfoList( Criterion $filter, $sort, $offset, $limit, $translations )
    {
        $query = $this->handler->createSelectQuery();
        $query->select(
            'DISTINCT ezcontentobject.id',
            'ezcontentobject.*',
            $this->handler->aliasedColumn( $query, 'main_node_id', 'main_tree' )
        );

        if ( $sort !== null )
        {
            $this->sortClauseConverter->applySelect( $query, $sort );
        }

        $query->from(
            $this->handler->quoteTable( 'ezcontentobject' )
        )->innerJoin(
            'ezcontentobject_version',
            'ezcontentobject.id',
            'ezcontentobject_version.contentobject_id'
        )->leftJoin(
            $this->handler->alias(
                $this->handler->quoteTable( 'ezcontentobject_tree' ),
                $this->handler->quoteIdentifier( 'main_tree' )
            ),
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->handler->quoteColumn( "contentobject_id", "main_tree" ),
                    $this->handler->quoteColumn( "id", "ezcontentobject" )
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn( "main_node_id", "main_tree" ),
                    $this->handler->quoteColumn( "node_id", "main_tree" )
                )
            )
        );

        if ( $sort !== null )
        {
            $this->sortClauseConverter->applyJoin( $query, $sort );
        }

        $query->where(
            $this->getQueryCondition( $filter, $query, $translations )
        );

        if ( $sort !== null )
        {
            $this->sortClauseConverter->applyOrderBy( $query );
        }

        $query->limit( $limit, $offset );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }
}

