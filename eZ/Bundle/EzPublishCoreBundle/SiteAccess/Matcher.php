<?php
/**
 * File containing the Siteaccess Matcher interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\SiteAccess;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher as BaseMatcher;

/**
 * Interface for service based siteaccess matchers.
 */
interface Matcher extends BaseMatcher
{
    /**
     * Registers the matching configuration associated with the matcher.
     *
     * @param mixed $matchingConfiguration
     */
    public function setMatchingConfiguration( $matchingConfiguration );
}
