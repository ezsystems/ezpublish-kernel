<?php
/**
 * File containing the ContentImageVariation class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

class ImageVariation extends ValueObjectVisitor
{
    /**
     * @param \eZ\Publish\SPI\Variation\Values\ImageVariation $data
     */
    public function visit( Visitor $visitor, Generator $generator, $data )
    {
        $generator->startObjectElement( 'ContentImageVariation' );

        $generator->startValueElement( 'uri', "/" . $data->uri );
        $generator->endValueElement( 'uri' );

        $generator->startValueElement( 'contentType', $data->mimeType );
        $generator->endValueElement( 'contentType' );

        $generator->startValueElement( 'width', $data->width );
        $generator->endValueElement( 'width' );

        $generator->startValueElement( 'height', $data->height );
        $generator->endValueElement( 'height' );

        $generator->startValueElement( 'fileSize', $data->fileSize );
        $generator->endValueElement( 'fileSize' );

        $generator->endObjectElement( 'ContentImageVariation' );
    }
}
