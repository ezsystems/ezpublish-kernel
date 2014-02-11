<?php
/**
 * File containing the LegacyExtensionsLocatorInterface interface.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\LegacyBundles;

interface LegacyExtensionsLocatorInterface
{
    /**
     * Locates legacy extensions within $path
     */
    public function locate( $path );
}
