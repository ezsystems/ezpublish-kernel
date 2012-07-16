<?php
/**
 * File containing the Section ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client\Output\ValueObjectVisitor;

use eZ\Publish\API\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\API\REST\Common\Output\Generator;
use eZ\Publish\API\REST\Common\Output\Visitor;

/**
 * Section value object visitor
 */
class SectionIncludingContentMetadataUpdateStruct extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param Visitor $visitor
     * @param Generator $generator
     * @param mixed $data
     * @return void
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startElement( 'ContentUpdate' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'ContentUpdate' ) );

        $generator->startElement( 'Section' );

        if ( $data->sectionId !== null )
        {
            $generator->startAttribute(
                'href',
                $data->sectionId
            );
            $generator->endAttribute( 'href' );
        }
        $generator->endElement( 'Section' );

        $generator->startElement( 'Owner', 'User' );
        if ( $data->ownerId !== null )
        {
            $generator->startAttribute(
                'href',
                $data->ownerId
            );
            $generator->endAttribute( 'href' );
        }
        $generator->endElement( 'Owner' );

        // TODO: Add missing elements

        $generator->endElement( 'ContentUpdate' );
    }
}

