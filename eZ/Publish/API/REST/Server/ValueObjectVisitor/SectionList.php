<?php
/**
 * File containing the SectionList visitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\ValueObjectVisitor;
use eZ\Publish\API\REST\Server\ValueObjectVisitor;
use eZ\Publish\API\REST\Server\Visitor;

/**
 * SectionList value object visitor
 */
class SectionList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param Visitor $visitor
     * @param mixed $data
     * @return void
     */
    public function visit( Visitor $visitor, $data )
    {
        $this->generator->startElement( 'SectionList' );
        $visitor->setHeader( 'Content-Type', $this->generator->getMediaType( 'SectionList' ) );

        $this->generator->startAttribute( 'href', '/content/sections' );
        $this->generator->endAttribute( 'href' );

        $this->generator->startList( 'section' );
        foreach ( $data->sections as $section )
        {
            $visitor->visitValueObject( $section );
        }
        $this->generator->endList( 'section' );

        $this->generator->endElement( 'SectionList' );
    }
}

