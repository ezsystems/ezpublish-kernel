<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator;

use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator;

/**
 * Puts variations in the an _alias/<aliasName> subfolder.
 *
 * Example:
 * my/image/file.jpg -> _aliases/large/my/image/file.jpg
 */
class AliasDirectoryVariationPathGenerator implements VariationPathGenerator
{
    public function getVariationPath($originalPath, $filter)
    {
        $info = pathinfo($originalPath);

        return sprintf(
            '_aliases/%s/%s/%s%s',
            $filter,
            $info['dirname'],
            $info['filename'],
            empty($info['extension']) ? '' : '.' . $info['extension']
        );
    }
}
