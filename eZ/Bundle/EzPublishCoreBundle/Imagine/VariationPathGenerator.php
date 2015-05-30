<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

interface VariationPathGenerator
{
    public function getVariationPath( $originalPath, $filter );
}
