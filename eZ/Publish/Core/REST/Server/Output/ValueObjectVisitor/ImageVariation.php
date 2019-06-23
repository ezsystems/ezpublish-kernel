<?php

/**
 * File containing the ContentImageVariation class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\SPI\Variation\Values\ImageVariation as ImageVariationValue;

class ImageVariation extends ValueObjectVisitor
{
    /**
     * @param \eZ\Publish\SPI\Variation\Values\ImageVariation $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ContentImageVariation');
        $this->visitImageVariationAttributes($visitor, $generator, $data);
        $generator->endObjectElement('ContentImageVariation');
    }

    protected function visitImageVariationAttributes(Visitor $visitor, Generator $generator, ImageVariationValue $data)
    {
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_binaryContent_getImageVariation',
                [
                    'imageId' => $data->imageId,
                    'variationIdentifier' => $data->name,
                ]
            )
        );
        $generator->endAttribute('href');

        $generator->startValueElement('uri', $data->uri);
        $generator->endValueElement('uri');

        if ($data->mimeType) {
            $generator->startValueElement('contentType', $data->mimeType);
            $generator->endValueElement('contentType');
        }

        if ($data->width) {
            $generator->startValueElement('width', $data->width);
            $generator->endValueElement('width');
        }

        if ($data->height) {
            $generator->startValueElement('height', $data->height);
            $generator->endValueElement('height');
        }

        if ($data->fileSize) {
            $generator->startValueElement('fileSize', $data->fileSize);
            $generator->endValueElement('fileSize');
        }
    }
}
