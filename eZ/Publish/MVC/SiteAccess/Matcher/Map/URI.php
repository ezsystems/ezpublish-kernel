<?php
/**
 * File containing the eZ\Publish\MVC\SiteAccess\Matcher\Map\URI class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess\Matcher\Map;

use eZ\Publish\MVC\SiteAccess\Matcher,
    eZ\Publish\MVC\SiteAccess\Matcher\Map,
    eZ\Publish\MVC\SiteAccess\URIFixer;

class URI extends Map implements Matcher, URIFixer
{
    /**
     * Constructor.
     *
     * @param array $URIElements Elements of the URI as parsed by parse_url().
     * @param array $siteAccessesConfiguration SiteAccesses configuration.
     */
    public function __construct( array $URIElements, array $siteAccessesConfiguration )
    {
        sscanf( isset( $URIElements["path"] ) ? $URIElements["path"] : "", "/%[^/]", $key );
        parent::__construct( $siteAccessesConfiguration, $key );
    }

    public function getName()
    {
        return 'uri:map';
    }

    /**
     * Fixes up $uri to remove the siteaccess part, if needed.
     *
     * @param string $uri The original URI
     * @return string
     */
    public function fixupURI( $uri )
    {
        return str_replace( "/$this->key", '', $uri );
    }

    /**
     * Fixes up $linkUri when generating a link to a route, in order to have the siteaccess part back in the URI.
     *
     * @param string $linkUri
     * @return string
     */
    public function fixupLink( $linkUri )
    {
        if ( strpos( $linkUri, $this->key ) === false )
        {
            $linkUri = '/' . $this->key . $linkUri;
        }

        return $linkUri;
    }
}
