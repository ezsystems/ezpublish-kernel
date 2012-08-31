<?php
/**
 * File containing the eZ\Publish\MVC\SiteAccess\Matcher\Map\Host class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess\Matcher\Map;

use eZ\Publish\MVC\SiteAccess\Matcher,
    eZ\Publish\MVC\SiteAccess\Matcher\Map,
    eZ\Publish\MVC\Routing\SimplifiedRequest;

class Host extends Map implements Matcher
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
        return 'host:map';
    }

    /**
     * Injects the request object to match against.
     *
     * @param \eZ\Publish\MVC\Routing\SimplifiedRequest $request
     * @return void
     */
    public function setRequest( SimplifiedRequest $request )
    {
        $this->setMapKey( $request->host );
    }
}
