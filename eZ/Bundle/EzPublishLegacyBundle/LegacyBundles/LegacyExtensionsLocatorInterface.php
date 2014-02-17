<?php
/**
 * File containing the LegacyExtensionsLocatorInterface interface.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
