<?php
/**
 * File containing the RestLocation ValueObjectVisitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
            $this->router->generate(
                'ezpublish_rest_loadLocation',
                array( 'locationPath' => trim( $data->location->pathString, '/' ) )
            )
        );
        $generator->endAttribute( 'href' );

        $generator->startValueElement( 'id', $data->location->id );
        $generator->endValueElement( 'id' );

        $generator->startValueElement( 'priority', $data->location->priority );
        $generator->endValueElement( 'priority' );

        $generator->startValueElement(
            'hidden',
            $this->serializeBool( $generator, $data->location->hidden )
        );
        $generator->endValueElement( 'hidden' );

        $generator->startValueElement(
            'invisible',
            $this->serializeBool( $generator, $data->location->invisible )
        );
        $generator->endValueElement( 'invisible' );

        $generator->startObjectElement( 'ParentLocation', 'Location' );
        if ( trim( $data->location->pathString, "/" ) !== '1' )
        {
            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'ezpublish_rest_loadLocation',
                    array(
                        'locationPath' => implode( '/', array_slice( $data->location->path, 0, count( $data->location->path ) - 1 ) )
                    )
                )
            );
            $generator->endAttribute( 'href' );
        }
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
            $this->router->generate(
                'ezpublish_rest_loadLocationChildren',
                array(
                    'locationPath' => trim( $data->location->pathString, '/' )
                )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Children' );

        $generator->startObjectElement( 'Content' );
        $generator->startAttribute(
            'href',
            $this->router->generate( 'ezpublish_rest_loadContent', array( 'contentId' => $data->location->contentId ) )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'Content' );

        $generator->startValueElement( 'sortField', $this->serializeSortField( $data->location->sortField ) );
        $generator->endValueElement( 'sortField' );

        $generator->startValueElement( 'sortOrder', $this->serializeSortOrder( $data->location->sortOrder ) );
        $generator->endValueElement( 'sortOrder' );

        $generator->startObjectElement( 'UrlAliases', 'UrlAliasRefList' );
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_listLocationURLAliases',
                array(
                    'locationPath' => trim( $data->location->pathString, '/' )
                )
            )
        );
        $generator->endAttribute( 'href' );
        $generator->endObjectElement( 'UrlAliases' );

        $generator->endObjectElement( 'Location' );
    }
}
