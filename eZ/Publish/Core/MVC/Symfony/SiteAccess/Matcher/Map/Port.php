<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\Port class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;

class Port extends Map implements Matcher
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

    public function getName()
    {
        return 'port';
    }

    /**
     * Injects the request object to match against.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request
     *
     * @return void
     */
    public function setRequest( SimplifiedRequest $request )
    {
        if ( !empty( $request->port ) )
        {
            $key = $request->port;
        }
        else
        {
            switch ( $request->scheme )
            {
                case "https":
                    $key = 443;
                    break;

                case "http":
                default:
                    $key = 80;
            }
        }

        $this->setMapKey( $key );
    }
}
