<?php
/**
 * File containing the Limitation ValueObjectVisitor class
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
 * Limitation value object visitor
 */
class Limitation extends ValueObjectVisitor
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
        $generator->startElement( 'limitation' );

        $generator->startAttribute( 'identifier', $data->getIdentifier() );
        $generator->endAttribute( 'identifier' );

        $generator->startElement( 'values' );
        $generator->startList( 'values' );

        foreach ( $data->limitationValues as $limitationValue )
        {
            $generator->startElement( 'ref' );
            $generator->startAttribute( 'href', $limitationValue );
            $generator->endAttribute( 'href' );
            $generator->endElement( 'ref' );
        }

        $generator->endList( 'values' );
        $generator->endElement( 'values' );

        $generator->endElement( 'limitation' );
    }
}
