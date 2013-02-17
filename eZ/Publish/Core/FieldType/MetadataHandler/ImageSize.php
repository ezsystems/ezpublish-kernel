<?php
/**
 * File containing the ImageSize class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\MetadataHandler;
use eZ\Publish\SPI\FieldType\MetadataHandler;

class ImageSize implements MetadataHandler
{
    public function extract( $filePath )
    {
        $metadata = getimagesize( $filePath );

        return array(
            'width' => $metadata[0],
            'height' => $metadata[1],
        );

    }
}
