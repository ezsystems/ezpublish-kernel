<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator;

use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator;

/**
 * Puts variations in the same folder than the original, suffixed with the filter name:.
 *
 * Example:
 * my/image/file.jpg -> my/image/file_large.jpg
 */
class OriginalDirectoryVariationPathGenerator implements VariationPathGenerator
{
    public function getVariationPath($originalPath, $filter)
    {
        $info = pathinfo($originalPath);

        return sprintf(
            '%s/%s_%s%s',
            $info['dirname'],
            $info['filename'],
            $filter,
            empty($info['extension']) ? '' : '.' . $info['extension']
        );
    }
}
