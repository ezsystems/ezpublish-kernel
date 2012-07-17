<?php
/**
 * File containing the SectionList visitor class
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
 * SectionList value object visitor
 */
class SectionList extends ValueObjectVisitor
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
        $generator->startElement( 'SectionList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'SectionList' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'sections' )
        );
        $generator->endAttribute( 'href' );

        $generator->startList( 'Section' );
        foreach ( $data->sections as $section )
        {
            $visitor->visitValueObject( $section );
        }
        $generator->endList( 'Section' );

        $generator->endElement( 'SectionList' );
    }
}

