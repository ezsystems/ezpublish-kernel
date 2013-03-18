<?php
/**
 * File containing the ContentTypeGroupUpdateStruct visitor class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

/**
 * ContentTypeGroupUpdateStruct value object visitor
 */
class ContentTypeGroupUpdateStruct extends ValueObjectVisitor
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
        $generator->startObjectElement( 'ContentTypeGroupInput' );
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'ContentTypeGroupInput' ) );

        $generator->startValueElement( 'identifier', $data->identifier );
        $generator->endValueElement( 'identifier' );

        if ( $data->modifierId !== null )
        {
            $generator->startObjectElement( 'User' );
            $generator->startAttribute( 'href', $data->modifierId );
            $generator->endAttribute( 'href' );
            $generator->endObjectElement( 'User' );
        }

        if ( $data->modificationDate !== null )
        {
            $generator->startValueElement( 'modificationDate', $data->modificationDate->format( 'c' ) );
            $generator->endValueElement( 'modificationDate' );
        }

        $generator->endObjectElement( 'ContentTypeGroupInput' );
    }
}
