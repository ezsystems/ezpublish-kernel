<?php

/**
 * File containing the VersatileMatcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
     * Note: VersatileMatcher objects always receive a request with cleaned up pathinfo (i.e. no SiteAccess part inside).
     *
     * @param string $siteAccessName
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess\VersatileMatcher|null Typically the current matcher, with updated request.
     */
    public function reverseMatch($siteAccessName);

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
