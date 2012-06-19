<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;

use eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Search\Handler as SearchHandlerInterface,
    eZ\Publish\SPI\Persistence\Content\Search\Result,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentTypeId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\RemoteId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationRemoteId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\SectionId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\UserMetadata,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\Status,
    eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound,
    Exception;

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
class SearchHandler extends SearchHandlerInterface
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
     * @see \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    public function find( Criterion $criterion, $offset = 0, $limit = null, array $sort = null, $translations = null )
    {
        // Only some criteria are supported as getting full support for all in InMemory engine is not a priority
        $match = array();
        self::generateMatchByCriteria( array( $criterion ), $match );

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
                    'match' => array( 'contentId' => 'id' )
                ),
                'contentInfo' => array(
                    'type' => 'Content\\ContentInfo',
                    'match' => array( 'id' => 'id' ),
                    'single' => true
                ),
                'versionInfo' => array(
                    'type' => 'Content\\VersionInfo',
                    'match' => array( 'contentId' => 'id', 'versionNo' => '_currentVersionNo' ),
                    'single' => true
                ),
            )
        );

        $resultList = array();
        foreach ( $list as $item )
        {
            if ( $item->contentInfo instanceof ContentInfo )
            {
                $item->fields = $this->backend->find(
                    'Content\\Field',
                    array(
                        '_contentId' => $item->contentInfo->id,
                        'versionNo' => $item->contentInfo->currentVersionNo
                    )
                );

                $resultList[] = $item;
            }
        }

        $result = new Result();
        $result->count = count( $resultList );

        if ( empty( $resultList ) || ( $limit === null && $offset === 0 ) )
            $result->content = $resultList;
        else if ( $limit === null )
             $result->content = array_slice( $resultList, $offset );
        else
            $result->content = array_slice( $resultList, $offset, $limit );

        return $result;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    public function findSingle( Criterion $criterion, $translations = null )
    {
        $list = $this->find( $criterion, 0, 1, null, $translations );
        if ( !$list->count )
            throw new NotFound( 'Content', var_export( $criterion, true ) );

        return $list->content[0];
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    public function indexContent( Content $content )
    {
        throw new Exception( "Not implemented yet." );
    }

    /**
     * Generate match array for use with Backend based on criteria
     *
     * @param array $criteria
     * @param array $match
     * @return void
     */
    protected static function generateMatchByCriteria( array $criteria, array &$match )
    {
        foreach ( $criteria as $criterion )
        {
            if ( $criterion instanceof LogicalAnd )
            {
                self::generateMatchByCriteria( $criterion->criteria, $match );
            }
            else if ( $criterion instanceof ContentId && !isset( $match['id'] ) )
            {
                $match['id'] = $criterion->value[0];
            }
            else if ( $criterion instanceof ContentTypeId && !isset( $match['contentInfo']['typeId'] ) )
            {
                $match['contentInfo']['contentTypeId'] = $criterion->value[0];
            }
            else if ( $criterion instanceof LocationId && !isset( $match['locations']['id'] ) )
            {
                $match['locations']['id'] = $criterion->value[0];
            }
            else if ( $criterion instanceof RemoteId && !isset( $match['contentInfo']['remoteId'] ) )
            {
                $match['contentInfo']['remoteId'] = $criterion->value[0];
            }
            else if ( $criterion instanceof LocationRemoteId && !isset( $match['locations']['remoteId'] ) )
            {
                $match['locations']['remoteId'] = $criterion->value[0];
            }
            else if ( $criterion instanceof SectionId && !isset( $match['contentInfo']['sectionId'] ) )
            {
                $match['contentInfo']['sectionId'] = $criterion->value[0];
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
                $match['locations']['parentId'] = $criterion->value[0];
            }
            else if ( $criterion instanceof Subtree && !isset( $match['locations']['pathString'] ) )
            {
                $match['locations']['pathString'] = $criterion->value[0] . '%';
            }
            else
            {
                if ( $criterion instanceof UserMetadata && $criterion->target !== $criterion::MODIFIER )
                {
                    if ( $criterion->target === $criterion::OWNER && !isset( $match['contentInfo']['ownerId'] ) )
                        $match['ownerId'] = $criterion->value[0];
                    else if ( $criterion->target === $criterion::CREATOR && !isset( $match['versionInfo']['creatorId'] ) )
                        $match['versionInfo']['creatorId'] = $criterion->value[0];
                    //else if ( $criterion->target === $criterion::MODIFIER && !isset( $match['version']['creatorId'] ) )
                        //$match['version']['creatorId'] = $criterion->value[0];
                    continue;
                }
                throw new Exception( "Support for provided criterion not supported or used more then once: " . get_class( $criterion ) );
            }
        }
    }
}
