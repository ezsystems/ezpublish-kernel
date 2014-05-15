<?php
/**
 * File containing the LegacyExtensionsLocatorInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\LegacyBundles;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;

interface LegacyExtensionsLocatorInterface
{
    /**
     * Returns the path to legacy extensions within $path
     *
     * @param string $path directory path
     * @return array An array of path to legacy extensions
     */
    public function getExtensionDirectories( $path );

    /**
     * Returns the list of legacy extension names in $bundle
     *
     * @param BundleInterface $bundle
     * @return array An array of legacy extensions names
     */
    public function getExtensionNames( BundleInterface $bundle );
}
