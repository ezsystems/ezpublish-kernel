<?php
/**
 * File containing the BaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\ValueObjectVisitor;
use eZ\Publish\API\REST\Server\ValueObjectVisitor;
use eZ\Publish\API\REST\Server\Visitor;

class Section extends ValueObjectVisitor
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
        $this->generator->startElement( 'Section' );

        $this->generator->startAttribute( 'href', '/content/sections/' . $data->id );
        $this->generator->endAttribute( 'href' );

        $this->generator->startValueElement( 'sectionId', $data->id );
        $this->generator->endValueElement( 'sectionId' );

        $this->generator->startValueElement( 'identifier', $data->identifier );
        $this->generator->endValueElement( 'identifier' );

        $this->generator->startValueElement( 'name', $data->name );
        $this->generator->endValueElement( 'name' );

        $this->generator->endElement( 'Section' );
    }
}

