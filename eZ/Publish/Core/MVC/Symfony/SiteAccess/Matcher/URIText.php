<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIText class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher,
    eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;

class URIText extends Regex implements Matcher
{
    /**
     * Constructor.
     *
     * @param array $siteAccessesConfiguration SiteAccesses configuration.
     */
    public function __construct( array $siteAccessesConfiguration )
    {
        parent::__construct(
            "^/" .
            ( isset( $siteAccessesConfiguration["prefix"] ) ? preg_quote( $siteAccessesConfiguration["prefix"], "@" ) : "" ) .
            "(\w+)" .
            ( isset( $siteAccessesConfiguration["suffix"] ) ? preg_quote( $siteAccessesConfiguration["suffix"], "@" ) : "" ),
            1
        );
    }

    public function getName()
    {
        return 'uri:text';
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
        $this->setMatchElement( $request->pathinfo );
    }
}
