<?php
/**
 * File containing the EzcDatabase content locator gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use ezcQuerySelect;

/**
 * Content locator gateway implementation using the zeta handler component.
 */
class EzcDatabase extends Gateway
{
    /**
     * 2^30, since PHP_INT_MAX can cause overflows in DB systems, if PHP is run
     * on 64 bit systems
     */
    const MAX_LIMIT = 1073741824;

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
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder
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
     * @param \EzcDbHandler $handler
     *
     * @return void
     */
    public function __construct(
        EzcDbHandler $handler,
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
     * Returns a list of object satisfying the $criterion.
     *
     * @todo Check Query recreation in this method. Something breaks if we reuse
     *       the query, after we have added the applyJoin() stuff here.
     *
     * @param Criterion $criterion
     * @param int $offset
     * @param int|null $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause[] $sort
     * @param string[] $translations
     *
     * @return mixed[][]
     */
    public function find( Criterion $criterion, $offset = 0, $limit = null, array $sort = null, array $translations = null )
    {
        $limit = $limit !== null ? $limit : self::MAX_LIMIT;

        // Get full object count
        $query = $this->handler->createSelectQuery();
        $condition = $this->getQueryCondition( $criterion, $query, $translations );

        $query
            ->select( 'COUNT( * )' )
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
            $query->expr->eq(
                'ezcontentobject_version.status',
                VersionInfo::STATUS_PUBLISHED
            ),
            $condition
        );

        $statement = $query->prepare();
        $statement->execute();

        $count = (int)$statement->fetchColumn();

        if ( $count === 0 || $limit === 0 )
        {
            return array( 'count' => $count, 'rows' => array() );
        }

        // Get clean query, Not sure why we cannot reuse the existing query
        // and conditions.
        $query = $this->handler->createSelectQuery();
        $condition = $this->getQueryCondition( $criterion, $query, $translations );

        $query
            ->select( 'COUNT( * )' )
            ->from( $this->handler->quoteTable( 'ezcontentobject' ) )
            ->where( $condition );

        $contentIds = $this->getContentIds( $query, $condition, $sort, $offset, $limit );

        return array(
            'count' => $count,
            'rows' => $this->loadContent( $contentIds, $translations ),
        );
    }

    /**
     * Get query condition
     *
     * @param Criterion $criterion
     * @param \ezcQuerySelect $query
     * @param mixed $translations
     *
     * @return string
     */
    protected function getQueryCondition( Criterion $criterion, ezcQuerySelect $query, $translations )
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
     * @param \ezcQuerySelect mixed $query
     * @param string $condition
     * @param mixed $sort
     * @param int $offset
     * @param int $limit
     *
     * @return int[]
     */
    protected function getContentIds( ezcQuerySelect $query, $condition, $sort, $offset, $limit )
    {
        $query->reset();
        $query->select(
            $this->handler->quoteColumn( 'id', 'ezcontentobject' )
        );

        if ( $sort !== null )
        {
            $this->sortClauseConverter->applySelect( $query, $sort );
        }

        $query->from(
            $this->handler->quoteTable( 'ezcontentobject' )
        );

        if ( $sort !== null )
        {
            $this->sortClauseConverter->applyJoin( $query, $sort );
        }

        $query->where( $condition );

        if ( $sort !== null )
        {
            $this->sortClauseConverter->applyOrderBy( $query, $sort );
        }

        $query->limit( $limit, $offset );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_COLUMN );
    }

    /**
     * Loads the actual content based on the provided IDs
     *
     * @param array $contentIds
     * @param mixed $translations
     *
     * @return mixed[]
     */
    protected function loadContent( array $contentIds, $translations )
    {
        $loadQuery = $this->queryBuilder->createFindQuery( $translations );
        $loadQuery->where(
            $loadQuery->expr->eq(
                'ezcontentobject_version.status',
                VersionInfo::STATUS_PUBLISHED
            ),
            $loadQuery->expr->in(
                $this->handler->quoteColumn( 'id', 'ezcontentobject' ),
                $contentIds
            )
        );

        $statement = $loadQuery->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );

        // Sort array, as defined in the $contentIds array
        $contentIdOrder = array_flip( $contentIds );
        usort(
            $rows,
            function ( $current, $next ) use ( $contentIdOrder )
            {
                return $contentIdOrder[$current['ezcontentobject_id']] -
                    $contentIdOrder[$next['ezcontentobject_id']];
            }
        );

        foreach ( $rows as &$row )
        {
            $row['ezcontentobject_always_available'] = $this->languageMaskGenerator->isAlwaysAvailable( $row['ezcontentobject_language_mask'] );
            $row['ezcontentobject_main_language_code'] = $this->languageHandler->load( $row['ezcontentobject_initial_language_id'] )->languageCode;
            $row['ezcontentobject_version_languages'] = $this->languageMaskGenerator->extractLanguageIdsFromMask( $row['ezcontentobject_version_language_mask'] );
            $row['ezcontentobject_version_initial_language_code'] = $this->languageHandler->load( $row['ezcontentobject_version_initial_language_id'] )->languageCode;
        }

        return $rows;
    }
}

