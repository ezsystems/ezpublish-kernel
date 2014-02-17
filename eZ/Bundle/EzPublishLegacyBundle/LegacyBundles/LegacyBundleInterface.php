<?php
/**
 * File containing the LegacyBundleInterface class.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\LegacyBundles;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * By implementing this interface, a bundle can return custom name of legacy extensions that should be injected.
 */
interface LegacyBundleInterface
{
    /**
     * Returns a list of legacy extension names
     *
     * @return array List of legacy extension names to inject to ActiveExtensions
     */
    public function getLegacyExtensionsNames();
}
