<?php
/**
 * File containing the SiteAccessRouterInterface class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess;

use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;

interface SiteAccessRouterInterface
{
    /**
     * Performs SiteAccess matching given the $request.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request
     *
     * @throws \eZ\Publish\Core\MVC\Exception\InvalidSiteAccessException
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    public function match( SimplifiedRequest $request );

    /**
     * Matches a SiteAccess by name.
     * Returns corresponding SiteAccess object, according to configuration, with corresponding matcher.
     * If no matcher can be found (e.g. non versatile), matcher property will be "default".
     *
     * @param string $siteAccessName
     *
     * @throws \InvalidArgumentException If $siteAccessName is invalid (i.e. not present in configured list).
     *
     * @return \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    public function matchByName( $siteAccessName );
}
