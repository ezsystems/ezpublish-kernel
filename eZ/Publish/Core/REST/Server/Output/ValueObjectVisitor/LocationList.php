<?php
/**
 * File containing the LocationList visitor class
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
 * LocationList value object visitor
 */
class LocationList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param mixed $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startElement( 'LocationList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'LocationList' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'objectLocations', array( 'object' => $data->contentId ) )
        );
        $generator->endAttribute( 'href' );

        $generator->startList( 'Location' );

        foreach ( $data->locations as $location )
        {
            $generator->startElement( 'Location' );
            $generator->startAttribute(
                'href',
                $this->urlHandler->generate( 'location', array( 'location' => trim( $location->pathString, '/' ) ) )
            );
            $generator->endAttribute( 'href' );
            $generator->endElement( 'Location' );
        }

        $generator->endList( 'Location' );

        $generator->endElement( 'LocationList' );
    }
}

