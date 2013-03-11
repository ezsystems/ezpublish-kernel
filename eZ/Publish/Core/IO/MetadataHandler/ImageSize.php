<?php
/**
 * File containing the ImageSize class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\MetadataHandler;
use eZ\Publish\Core\IO\MetadataHandler;

class ImageSize implements MetadataHandler
{
    public function extract( $filePath )
    {
        $metadata = getimagesize( $filePath );

        return array(
            'width' => $metadata[0],
            'height' => $metadata[1],
            // required until a dedicated mimetype metadata handler is added
            'mime' => $metadata['mime'],
        );

    }
}
