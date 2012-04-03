<?php
/**
 * File containing the Section ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\ValueObjectVisitor;
use eZ\Publish\API\REST\Server\ValueObjectVisitor;
use eZ\Publish\API\REST\Server\Generator;
use eZ\Publish\API\REST\Server\Visitor;

/**
 * Section value object visitor
 */
class Section extends ValueObjectVisitor
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
        $generator->startElement( 'Section' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'Section' ) );

        $generator->startAttribute( 'href', '/content/sections/' . $data->id );
        $generator->endAttribute( 'href' );

        $generator->startValueElement( 'sectionId', $data->id );
        $generator->endValueElement( 'sectionId' );

        $generator->startValueElement( 'identifier', $data->identifier );
        $generator->endValueElement( 'identifier' );

        $generator->startValueElement( 'name', $data->name );
        $generator->endValueElement( 'name' );

        $generator->endElement( 'Section' );
    }
}

