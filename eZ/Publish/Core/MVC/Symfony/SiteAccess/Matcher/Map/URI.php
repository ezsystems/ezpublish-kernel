<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\URI class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;

class URI extends Map implements Matcher, URILexer
{
    /**
     * Injects the request object to match against.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request
     *
     * @return void
     */
    public function setRequest( SimplifiedRequest $request )
    {
        sscanf( $request->pathinfo, "/%[^/]", $key );
        $this->setMapKey( $key );
    }

    public function getName()
    {
        return 'uri:map';
    }

    /**
     * Fixes up $uri to remove the siteaccess part, if needed.
     *
     * @param string $uri The original URI
     *
     * @return string
     */
    public function analyseURI( $uri )
    {
        return str_replace( "/$this->key", '', $uri );
    }

    /**
     * Analyses $linkUri when generating a link to a route, in order to have the siteaccess part back in the URI.
     *
     * @param string $linkUri
     *
     * @return string The modified link URI
     */
    public function analyseLink( $linkUri )
    {
        // Joining slash between uriElements and actual linkUri must be present, except if $linkUri is empty.
        $joiningSlash = empty( $linkUri ) ? '' : '/';
        $linkUri = ltrim( $linkUri, '/' );
        // Removing query string to analyse as SiteAccess might be in it.
        $qsPos = strpos( $linkUri, '?' );
        $queryString = '';
        if ( $qsPos !== false )
        {
            $queryString = substr( $linkUri, $qsPos );
            $linkUri = substr( $linkUri, 0, $qsPos );
        }

        if ( strpos( $linkUri, $this->key ) === false )
        {
            $linkUri = "/{$this->key}{$joiningSlash}{$linkUri}";
        }

        return $linkUri . $queryString;
    }

}
