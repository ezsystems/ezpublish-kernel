<?php
/**
 * File containing the VersatileMatcher class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess;

/**
 * Interface for SiteAccess matchers.
 *
 * VersatileMatcher makes it possible to do a reverse match (e.g. "Is this matcher knows provided SiteAccess name?").
 * Versatile matchers enable cross-siteAccess linking.
 */
interface VersatileMatcher extends Matcher
{
    /**
     * Returns matcher object corresponding to $siteAccessName or null if non applicable.
     *
     * @param string $siteAccessName
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher|null Typically a clone of current matcher, with appropriate config.
     */
    public function reverseMatch( $siteAccessName );

    /**
     * Returns the SimplifiedRequest object corresponding to the reverse match.
     * This request object can then be used to build a link to the "reverse matched" SiteAccess.
     *
     * @see reverseMatch()
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest
     */
    public function getRequest();
}
