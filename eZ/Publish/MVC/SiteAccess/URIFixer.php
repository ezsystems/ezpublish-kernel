<?php
/**
 * File containing the URIFixer class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess;

/**
 * Interface for SiteAccess matchers that need to fixup the URI after matching.
 * This is useful when you have the siteaccess in the URI like "/<siteaccessName>/my/awesome/uri"
 */
interface URIFixer
{
    /**
     * Fixes up $uri to remove the siteaccess part, if needed.
     *
     * @param string $uri The original URI
     * @return string
     */
    public function fixupURI( $uri );

    /**
     * Fixes up $linkUri when generating a link to a route, in order to have the siteaccess part back in the URI.
     *
     * @param string $linkUri
     * @return string
     */
    public function fixupLink( $linkUri );
}
