<?php
/**
 * File containing the LegacyBundleInterface class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishLegacyBundle\LegacyBundles;

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
