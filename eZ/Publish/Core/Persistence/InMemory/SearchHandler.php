<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Search\Handler as SearchHandlerInterface;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\RemoteId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationRemoteId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\SectionId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserMetadata;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ObjectStateId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Status;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Exception;

/**
 * The Content Search handler retrieves sets of of Content objects, based on a
 * set of criteria.
 *
 * The basic idea of this class is to do the following:
 *
 * 1) The find methods retrieve a recursive set of filters, which define which
 * content objects to retrieve from the database. Those may be combined using
 * boolean operators.
 *
 * 2) This recursive criterion definition is visited into a query, which limits
 * the content retrieved from the database. We might not be able to create
 * sensible queries from all criterion definitions.
 *
 * 3) The query might be possible to optimize (remove empty statements),
 * reduce singular and and or constructs…
 *
 * 4) Additionally we might need a post-query filtering step, which filters
 * content objects based on criteria, which could not be converted in to
 * database statements.
 */
class SearchHandler implements SearchHandlerInterface
{
    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to Handler object that created it.
     *
     * @param Handler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( Handler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * Finds content objects for the given query.
     *
     * @todo define structs for the field filters
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findContent( Query $query, array $fieldFilters = array() )
    {
        // Only some criteria are supported as getting full support for all in InMemory engine is not a priority
        $match = array();
        $this->generateMatchByCriteria( array( $query->criterion ), $match );

        if ( empty( $match ) )
        {
            throw new Exception( "Logical error: \$match is empty" );
        }

        $list = $this->backend->find(
            'Content',
            $match,
            array(
                'locations' => array(
                    'type' => 'Content\\Location',
                    'match' => array( 'contentId' => 'id' ),
                    'skip' => true
                ),
                'versionInfo' => array(
                    'type' => 'Content\\VersionInfo',
                    'match' => array( '_contentId' => 'id', 'versionNo' => '_currentVersionNo' ),
                    'single' => true,
                    'sub' => array(
                        'contentInfo' => array(
                            'type' => 'Content\\ContentInfo',
                            'match' => array( 'id' => '_contentId' ),
                            'single' => true
                        ),
                    )
                ),
                'objectStates' => array(
                    'type' => 'Content\\ObjectState',
                    'match' => array( '_contentId' => 'id' ),
                    'skip' => true
                )
            )
        );

        $resultList = array();
        foreach ( $list as $item )
        {
            if ( !$item instanceof Content )
                throw new \RuntimeException( "item is not instance of Content, it is ". var_export( $item, true ) );
            else if ( !$item->versionInfo instanceof VersionInfo )
                throw new \RuntimeException( "item->versionInfo is not instance of VersionInfo, it is ". var_export( $item, true ) );
            else if ( !$item->versionInfo->contentInfo instanceof ContentInfo )
                throw new \RuntimeException( "item->versionInfo->contentInfo is not instance of ContentInfo, it is ". var_export( $item, true ) );
            else
            {
                $item->fields = $this->backend->find(
                    'Content\\Field',
                    array(
                        '_contentId' => $item->versionInfo->contentInfo->id,
                        'versionNo' => $item->versionInfo->contentInfo->currentVersionNo
                    )
                );

                $resultList[] = $item;
            }
        }

        $result = new SearchResult();
        $result->time = 0;
        $result->totalCount = count( $resultList );

        if ( empty( $resultList ) || ( $query->limit === null && $query->offset === 0 ) )
            $result->searchHits = $resultList;
        else if ( $query->limit === null )
             $result->searchHits = array_slice( $resultList, $query->offset );
        else
            $result->searchHits = array_slice( $resultList, $query->offset, $query->limit );

        $result->searchHits = array_map(
            function ( $content )
            {
                return new SearchHit(
                    array(
                        'valueObject' => $content,
                    )
                );
            },
            $result->searchHits
        );

        return $result;
    }

    /**
     * Performs a query for a single content object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the object was not found by the query or due to permissions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is more than than one result matching the criterions
     *
     * @todo define structs for the field filters
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function findSingle( Criterion $criterion, array $fieldFilters = array() )
    {
        $list = $this->findContent( new Query( array( 'criterion' => $criterion ) ) );

        if ( !$list->totalCount )
            throw new NotFound( 'Content', var_export( $criterion, true ) );
        else if ( $list->totalCount > 1 )
            throw new InvalidArgumentException( "totalCount", "findSingle() found more then one item for query" );

        return $list->searchHits[0]->valueObject;
    }

    /**
     * Suggests a list of values for the given prefix
     *
     * @param string $prefix
     * @param string[] $fieldPaths
     * @param int $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     */
    public function suggest( $prefix, $fieldPaths = array(), $limit = 10, Criterion $filter = null )
    {
        throw new \Exception( "Not implemented yet." );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    public function indexContent( Content $content )
    {
        throw new \Exception( "Not implemented yet." );
    }

    /**
     * Deletes a content object from the index
     *
     * @param int $contentId
     * @param int|null $versionId
     *
     * @return void
     */
    public function deleteContent( $contentId, $versionId = null )
    {
        throw new \Exception( "Not implemented yet." );
    }

    /**
     * Generate match array for use with Backend based on criteria
     *
     * @param array $criteria
     * @param array $match
     *
     * @return void
     */
    protected function generateMatchByCriteria( array $criteria, array &$match )
    {
        foreach ( $criteria as $criterion )
        {
            if ( $criterion instanceof LogicalAnd )
            {
                $this->generateMatchByCriteria( $criterion->criteria, $match );
            }
            else if ( $criterion instanceof ContentId && !isset( $match['id'] ) )
            {
                $match['id'] = $criterion->operator === Operator::IN ? $criterion->value : $criterion->value[0];
            }
            else if ( $criterion instanceof ContentTypeId && !isset( $match['versionInfo']['contentInfo']['typeId'] ) )
            {
                $match['versionInfo']['contentInfo']['contentTypeId'] = $criterion->operator === Operator::IN ? $criterion->value : $criterion->value[0];
            }
            else if ( $criterion instanceof LocationId && !isset( $match['locations']['id'] ) )
            {
                $match['locations']['id'] = $criterion->operator === Operator::IN ? $criterion->value : $criterion->value[0];
            }
            else if ( $criterion instanceof RemoteId && !isset( $match['versionInfo']['contentInfo']['remoteId'] ) )
            {
                $match['versionInfo']['contentInfo']['remoteId'] = $criterion->operator === Operator::IN ? $criterion->value : $criterion->value[0];
            }
            else if ( $criterion instanceof LocationRemoteId && !isset( $match['locations']['remoteId'] ) )
            {
                $match['locations']['remoteId'] = $criterion->operator === Operator::IN ? $criterion->value : $criterion->value[0];
            }
            else if ( $criterion instanceof ObjectStateId && !isset( $match['objectStates']['id'] ) )
            {
                $match['objectStates']['id'] = $criterion->operator === Operator::IN ? $criterion->value : $criterion->value[0];
            }
            else if ( $criterion instanceof LanguageCode && !isset( $match['versionInfo']['languageIds'] ) )
            {
                $languageHandler = $this->handler->contentLanguageHandler();
                $languageIds = array_map(
                    function ( $languageCode ) use ( $languageHandler )
                    {
                        return $languageHandler->loadByLanguageCode( $languageCode )->id;
                    },
                    $criterion->value
                );

                $match['versionInfo']['languageIds'] = $criterion->operator === Operator::IN ? $languageIds : $languageIds[0];
            }
            else if ( $criterion instanceof SectionId && !isset( $match['versionInfo']['contentInfo']['sectionId'] ) )
            {
                $match['versionInfo']['contentInfo']['sectionId'] = $criterion->operator === Operator::IN ? $criterion->value : $criterion->value[0];
            }
            else if ( $criterion instanceof Status && !isset( $match['versionInfo']['status'] ) )
            {
                switch ( $criterion->value[0] )
                {
                    case Status::STATUS_ARCHIVED:
                        $match['versionInfo']['status'] = VersionInfo::STATUS_ARCHIVED;
                        break;
                    case Status::STATUS_DRAFT:
                        $match['versionInfo']['status'] = VersionInfo::STATUS_DRAFT;
                        break;
                    case Status::STATUS_PUBLISHED:
                        $match['versionInfo']['status'] = VersionInfo::STATUS_PUBLISHED;
                        break;
                    default:
                        throw new Exception( "Unsupported StatusCriterion->value[0]: " . $criterion->value[0] );
                }
            }
            else if ( $criterion instanceof ParentLocationId && !isset( $match['locations']['parentId'] ) )
            {
                $match['locations']['parentId'] = $criterion->operator === Operator::IN ? $criterion->value : $criterion->value[0];
            }
            else if ( $criterion instanceof Subtree && !isset( $match['locations']['pathString'] ) )
            {
                $match['locations']['pathString'] = $criterion->value[0] . '%';
            }
            else
            {
                if ( $criterion instanceof UserMetadata && $criterion->target !== $criterion::MODIFIER )
                {
                    if ( $criterion->target === $criterion::OWNER && !isset( $match['versionInfo']['contentInfo']['ownerId'] ) )
                        $match['versionInfo']['contentInfo']['ownerId'] = $criterion->operator === Operator::IN ? $criterion->value : $criterion->value[0];
                    else if ( $criterion->target === $criterion::CREATOR && !isset( $match['versionInfo']['creatorId'] ) )
                        $match['versionInfo']['creatorId'] = $criterion->operator === Operator::IN ? $criterion->value : $criterion->value[0];
                    //else if ( $criterion->target === $criterion::MODIFIER && !isset( $match['version']['creatorId'] ) )
                        //$match['version']['creatorId'] = $criterion->value[0];
                    continue;
                }
                throw new Exception( "Support for provided criterion not supported or used more then once: " . get_class( $criterion ) );
            }
        }
    }
}
