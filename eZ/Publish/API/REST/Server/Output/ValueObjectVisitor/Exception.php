<?php
/**
 * File containing the Exception visitor class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\API\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\API\REST\Common\Output\Generator;
use eZ\Publish\API\REST\Common\Output\Visitor;

/**
 * Exception value object visitor
 */
class Exception extends ValueObjectVisitor
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
        $generator->startElement( 'Exception' );
        $visitor->setHeader( 'Status', '500 Internal Server Error' );

        // @TODO: What do we want here?
        $visitor->setHeader( 'Content-Type', $generator->getMediaType( 'Exception' ) );

        $generator->startAttribute( 'code', $data->getCode() );
        $generator->endAttribute( 'code' );

        $generator->startAttribute( 'file', $data->getFile() );
        $generator->endAttribute( 'file' );

        $generator->startAttribute( 'line', $data->getLine() );
        $generator->endAttribute( 'line' );

        $generator->startValueElement( 'message', $data->getMessage() );
        $generator->endValueElement( 'message' );

        $generator->startValueElement( 'trace', $data->getTraceAsString() );
        $generator->endValueElement( 'trace' );

        $generator->endElement( 'Exception' );
    }
}

