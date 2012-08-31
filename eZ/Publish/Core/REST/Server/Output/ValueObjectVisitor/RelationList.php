<?php
/**
 * File containing the RelationList visitor class
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
 * RelationList value object visitor
 */
class RelationList extends ValueObjectVisitor
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
        $generator->startObjectElement( 'Relations', 'RelationList' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'RelationList' ) );

        $generator->startAttribute(
            'href',
            $this->urlHandler->generate( 'objectrelations', array( 'object' => $data->contentId ) )
        );
        $generator->endAttribute( 'href' );

        $generator->startList( 'Relation' );
        foreach ( $data->relations as $section )
        {
            $visitor->visitValueObject( $section );
        }
        $generator->endList( 'Relation' );

        $generator->endObjectElement( 'Relations' );
    }
}

