<?php
/**
 * File containing the eZ\Publish\MVC\SiteAccess\Matcher interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess;

interface Matcher
{
    /**
     * Returns matching Siteaccess.
     *
     * @return string|false Siteaccess matched of false
     */
    public function match();
}