<?php
/**
 * File containing the ContentImageVariation class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_binaryContent_getImageVariation',
                array(
                    'imageId' => $data->imageId,
                    'variationIdentifier' => $data->name
                )
            )
        );
        $generator->endAttribute( 'href' );

        // @todo installation subfolder
        $generator->startValueElement( 'uri', "/" . $data->uri );
        $generator->endValueElement( 'uri' );

        if ( $data->mimeType )
        {
            $generator->startValueElement( 'contentType', $data->mimeType );
            $generator->endValueElement( 'contentType' );
        }

        if ( $data->width )
        {
            $generator->startValueElement( 'width', $data->width );
            $generator->endValueElement( 'width' );
        }

        if ( $data->height )
        {
            $generator->startValueElement( 'height', $data->height );
            $generator->endValueElement( 'height' );
        }

        if ( $data->fileSize )
        {
            $generator->startValueElement( 'fileSize', $data->fileSize );
            $generator->endValueElement( 'fileSize' );
        }

        $generator->endObjectElement( 'ContentImageVariation' );
    }
}
