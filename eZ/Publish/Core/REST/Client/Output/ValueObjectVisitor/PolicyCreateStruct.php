<?php
/**
 * File containing the policy create struct ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * Policy create struct value object visitor
 */
class PolicyCreateStruct extends ValueObjectVisitor
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
        $generator->startElement( 'PolicyCreate' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'PolicyCreate' ) );

        $generator->startValueElement( 'module', $data->module );
        $generator->endValueElement( 'module' );

        $generator->startValueElement( 'function', $data->function );
        $generator->endValueElement( 'function' );

        $limitations = $data->getLimitations();
        if ( !empty( $limitations ) )
        {
            $generator->startElement( 'limitations' );
            $generator->startList( 'limitations' );

            foreach ( $limitations as $limitation )
            {
                $visitor->visitValueObject( $limitation );
            }

            $generator->endList( 'limitations' );
            $generator->endElement( 'limitations' );
        }

        $generator->endElement( 'PolicyCreate' );
    }
}
