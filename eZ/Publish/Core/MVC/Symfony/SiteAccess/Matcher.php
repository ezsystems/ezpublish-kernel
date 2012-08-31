<?php
/**
 * File containing the eZ\Publish\MVC\SiteAccess\Matcher interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess;

use eZ\Publish\MVC\Routing\SimplifiedRequest;

/**
 * Interface for SiteAccess matchers.
 */
interface Matcher
{
    /**
     * Injects the request object to match against.
     *
     * @param \eZ\Publish\MVC\Routing\SimplifiedRequest $request
     * @return void
     */
    public function setRequest( SimplifiedRequest $request );

    /**
     * Returns matched Siteaccess or false if no siteaccess could be matched.
     *
     * @return string|false
     */
    public function match();

    /**
     * Returns the matcher's name.
     * This information will be stored in the SiteAccess object itself to quickly be able to identify the matcher type.
     *
     * @return string
     */
    public function getName();
}