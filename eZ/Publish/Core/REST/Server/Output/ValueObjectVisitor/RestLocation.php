<?php
/**
 * File containing the RestLocation ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * RestLocation value object visitor
 */
class RestLocation extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\RestLocation $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'Location' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'Location' ) );
        $visitor->setHeader( 'Accept-Patch', $generator->getMediaType( 'LocationUpdate' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'location', array( 'location' => rtrim( $data->location->pathString, '/' ) ) )
        );
        $generator->endAttribute( 'href' );

        $generator->startValueElement( 'id', $data->location->id );
        $generator->endValueElement( 'id' );

        $generator->startValueElement( 'priority', $data->location->priority );
        $generator->endValueElement( 'priority' );

        $generator->startValueElement( 'hidden', $data->location->hidden ? 'true' : 'false' );
        $generator->endValueElement( 'hidden' );

        $generator->startValueElement( 'invisible', $data->location->invisible ? 'true' : 'false' );
        $generator->endValueElement( 'invisible' );

        $generator->startObjectElement( 'ParentLocation', 'Location' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'location',
                array(
                    'location' => '/' . implode( '/', array_slice( $data->location->path, 0, count( $data->location->path ) - 1 ) )
                )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'ParentLocation' );

        $generator->startValueElement( 'pathString', $data->location->pathString );
        $generator->endValueElement( 'pathString' );

        $generator->startValueElement( 'depth', $data->location->depth );
        $generator->endValueElement( 'depth' );

        $generator->startValueElement( 'childCount', $data->childCount );
        $generator->endValueElement( 'childCount' );

        $generator->startValueElement( 'remoteId', $data->location->remoteId );
        $generator->endValueElement( 'remoteId' );

        $generator->startObjectElement( 'Children', 'LocationList' );
        $generator->startAttribute(
            'href',
            $this->urlHandler->generate(
                'locationChildren',
                array(
                    'location' => rtrim( $data->location->pathString, '/' )
                )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Children' );

        $generator->startObjectElement( 'Content' );
        $generator->startAttribute( 'href', $this->urlHandler->generate( 'object', array( 'object' => $data->location->contentId ) ) );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Content' );

        $generator->startValueElement( 'sortField', $this->serializeSortField( $data->location->sortField ) );
        $generator->endValueElement( 'sortField' );

        $generator->startValueElement( 'sortOrder', $this->serializeSortOrder( $data->location->sortOrder ) );
        $generator->endValueElement( 'sortOrder' );

        $generator->endObjectElement( 'Location' );
    }
}
