<?php
/**
 * File containing the Trash ValueObjectVisitor class
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
 * Trash value object visitor
 */
class Trash extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \eZ\Publish\Core\REST\Server\Values\Trash $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'Trash' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'Trash' ) );

        $generator->startAttribute( 'href', $data->path );
        $generator->endAttribute( 'href' );

        $generator->startList( 'TrashItem' );

        foreach ( $data->trashItems as $trashItem )
        {
            $visitor->visitValueObject( $trashItem );
        }

        $generator->endList( 'TrashItem' );

        $generator->endObjectElement( 'Trash' );
    }
}
