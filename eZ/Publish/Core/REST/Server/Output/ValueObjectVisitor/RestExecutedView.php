<?php
/**
 * File containing the Section ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values\RestContent as RestContentValue;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;

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
    protected $contentService;

    protected $locationService;

    public function __construct( UrlHandler $urlHandler, LocationService $locationService, ContentService $contentService )
    {
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        parent::__construct( $urlHandler );
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
            $this->urlHandler->generate( 'view', array( 'view' => $data->identifier ) )
        );
        $generator->endAttribute( 'href' );

        $generator->startValueElement( 'identifier', $data->identifier );
        $generator->endValueElement( 'identifier' );

        // BEGIN Query
        $generator->startObjectElement( 'Query' );
        $generator->endObjectElement( 'Query' );
        // END Query

        // BEGIN Result
        $generator->startObjectElement( 'Result', $generator->getMediaType( 'ViewResult' ) );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'viewResults', array( 'view' => $data->identifier ) )
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

            $contentInfo = $searchHit->valueObject->contentInfo;
            $restContent = new RestContentValue(
                $contentInfo,
                $this->locationService->loadLocation( $contentInfo->mainLocationId ),
                $searchHit->valueObject,
                $this->contentService->loadRelations( $searchHit->valueObject->getVersionInfo() )
            );
            $visitor->visitValueObject( $restContent );
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
}

