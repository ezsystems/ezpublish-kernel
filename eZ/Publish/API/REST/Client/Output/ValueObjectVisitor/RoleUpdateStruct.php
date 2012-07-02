<?php
/**
 * File containing the Role update struct ValueObjectVisitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client\Output\ValueObjectVisitor;

use eZ\Publish\API\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\API\REST\Common\Output\Generator;
use eZ\Publish\API\REST\Common\Output\Visitor;

/**
 * Role update struct value object visitor
 */
class RoleUpdateStruct extends ValueObjectVisitor
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
        $generator->startElement( 'RoleInput' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'RoleInput' ) );

        $generator->startValueElement( 'identifier', $data->identifier );
        $generator->endValueElement( 'identifier' );

        $generator->endElement( 'RoleInput' );
    }
}
