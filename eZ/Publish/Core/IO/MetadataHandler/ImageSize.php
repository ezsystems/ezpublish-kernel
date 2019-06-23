<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\MetadataHandler;

use eZ\Publish\Core\IO\MetadataHandler;

/**
 * @deprecated Not in use anymore by the kernel.
 */
class ImageSize implements MetadataHandler
{
    public function extract($filePath)
    {
        $metadata = getimagesize($filePath);

        return [
            'width' => $metadata[0],
            'height' => $metadata[1],
            // required until a dedicated mimetype metadata handler is added
            'mime' => $metadata['mime'],
        ];
    }
}
