<?php
/**
 * File containing the Section ValueObjectVisitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values\RestContent as RestContentValue;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\REST\Server\Values\RestLocation;

/**
 * Section value object visitor
 */
class RestExecutedView extends ValueObjectVisitor
{
    /**
     * Location service
     *
     * @var \eZ\Publish\API\Repository\LocationService
     */
    protected $locationService;

    /**
     * Content service
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * ContentType service
     *
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(
        LocationService $locationService,
        ContentService $contentService,
        ContentTypeService $contentTypeService
    )
    {
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestExecutedView $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'View' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'View' ) );

        $generator->startAttribute(
            'href',
            $this->router->generate( 'ezpublish_rest_getView', array( 'viewId' => $data->identifier ) )
        );
        $generator->endAttribute( 'href' );

        $generator->startValueElement( 'identifier', $data->identifier );
        $generator->endValueElement( 'identifier' );

        // BEGIN Query
        $generator->startObjectElement( 'Query' );
        $generator->endObjectElement( 'Query' );
        // END Query

        // BEGIN Result
        $generator->startObjectElement( 'Result', 'ViewResult' );
        $generator->startAttribute(
            'href',
            $this->router->generate( 'ezpublish_rest_loadViewResults', array( 'viewId' => $data->identifier ) )
        );
        $generator->endAttribute( 'href' );

        // BEGIN searchHits
        $generator->startHashElement( 'searchHits' );
        $generator->startList( 'searchHit' );

        foreach ( $data->searchResults->searchHits as $searchHit )
        {
            $generator->startObjectElement( 'searchHit' );

            $generator->startAttribute( 'score', 0 );
            $generator->endAttribute( 'score' );

            $generator->startAttribute( 'index', 0 );
            $generator->endAttribute( 'index' );

            $generator->startObjectElement( 'value' );

            $visitor->visitValueObject( $this->mapSearchHit( $searchHit ) );
            $generator->endObjectElement( 'value' );
            $generator->endObjectElement( 'searchHit' );
        }

        $generator->endList( 'searchHit' );

        $generator->endHashElement( 'searchHits' );
        // END searchHits

        $generator->endObjectElement( 'Result' );
        // END Result

        $generator->endObjectElement( 'View' );
    }

    private function mapSearchHit( SearchHit $searchHit )
    {
        if ( $searchHit->valueObject instanceof Content )
        {
            return $this->mapContentSearchHit( $searchHit->valueObject );
        }
        else if ( $searchHit->valueObject instanceof Location )
        {
            return $this->mapLocationSearchHit( $searchHit->valueObject );
        }
    }

    /**
     * @return RestContent
     */
    private function mapContentSearchHit( Content $content )
    {
        return new RestContentValue(
            $content->contentInfo,
            $this->locationService->loadLocation( $content->contentInfo->mainLocationId ),
            $content,
            $this->contentTypeService->loadContentType( $content->contentInfo->contentTypeId ),
            $this->contentService->loadRelations( $content->getVersionInfo() )
        );
    }

    /**
     * @return RestLocation
     */
    private function mapLocationSearchHit( Location $location )
    {
        return new RestLocation( $location, 0, true );
    }
}
