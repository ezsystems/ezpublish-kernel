<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map\Port class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\Map;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;

class Port extends Map
{
    public function getName()
    {
        return 'port';
    }

    /**
     * Injects the request object to match against.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest $request
     */
    public function setRequest( SimplifiedRequest $request )
    {
        if ( !$this->key )
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

        parent::setRequest( $request );
    }

    public function reverseMatch( $siteAccessName )
    {
        $matcher = parent::reverseMatch( $siteAccessName );
        if ( $matcher instanceof Port )
        {
            $matcher->getRequest()->setPort( $matcher->getMapKey() );
        }

        return $matcher;
    }
}
