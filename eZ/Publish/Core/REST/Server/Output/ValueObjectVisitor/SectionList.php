<?php
/**
 * File containing the SectionList ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
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
     * @param \eZ\Publish\Core\REST\Server\Values\SectionList $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'SectionList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'SectionList' ) );
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader( 'Accept-Patch', false );

        $generator->startAttribute( 'href', $data->path );
        $generator->endAttribute( 'href' );

        $generator->startList( 'Section' );
        foreach ( $data->sections as $section )
        {
            $visitor->visitValueObject( $section );
        }
        $generator->endList( 'Section' );

        $generator->endObjectElement( 'SectionList' );
    }
}
