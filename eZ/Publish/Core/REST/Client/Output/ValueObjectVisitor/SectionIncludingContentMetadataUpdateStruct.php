<?php
/**
 * File containing the SectionIncludingContentMetadataUpdateStruct ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * SectionIncludingContentMetadataUpdateStruct value object visitor
 */
class SectionIncludingContentMetadataUpdateStruct extends ValueObjectVisitor
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
        $generator->startObjectElement( 'ContentUpdate' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'ContentUpdate' ) );

        $generator->startObjectElement( 'Section' );

        if ( $data->sectionId !== null )
        {
            $generator->startAttribute(
                'href',
                $data->sectionId
            );
            $generator->endAttribute( 'href' );
        }
        $generator->endObjectElement( 'Section' );

        $generator->startObjectElement( 'Owner', 'User' );
        if ( $data->ownerId !== null )
        {
            $generator->startAttribute(
                'href',
                $data->ownerId
            );
            $generator->endAttribute( 'href' );
        }
        $generator->endObjectElement( 'Owner' );

        // @todo: Add missing elements

        $generator->endObjectElement( 'ContentUpdate' );
    }
}
