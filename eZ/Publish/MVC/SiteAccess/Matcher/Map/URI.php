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
    eZ\Publish\MVC\Routing\SimplifiedRequest,
    eZ\Publish\MVC\SiteAccess\URILexer;

class URI extends Map implements Matcher, URILexer
{
    /**
     * Constructor.
     *
     * @param array $siteAccessesConfiguration SiteAccesses configuration.
     */
    public function __construct( array $siteAccessesConfiguration )
    {
        parent::__construct( $siteAccessesConfiguration );
    }

    /**
     * Injects the request object to match against.
     *
     * @param \eZ\Publish\MVC\Routing\SimplifiedRequest $request
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
     * @return string The modified link URI
     */
    public function analyseLink( $linkUri )
    {
        if ( strpos( $linkUri, $this->key ) === false )
        {
            $linkUri = '/' . $this->key . $linkUri;
        }

        return $linkUri;
    }

}
