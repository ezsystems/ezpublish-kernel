<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIText class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;

class URIText extends Regex implements Matcher, URILexer
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $suffix;

    /**
     * Constructor.
     *
     * @param array $siteAccessesConfiguration SiteAccesses configuration.
     */
    public function __construct( array $siteAccessesConfiguration )
    {
        $this->prefix = isset( $siteAccessesConfiguration['prefix'] ) ? $siteAccessesConfiguration['prefix'] : '';
        $this->suffix = isset( $siteAccessesConfiguration['suffix'] ) ? $siteAccessesConfiguration['suffix'] : '';

        parent::__construct(
            '^(/' . preg_quote( $this->prefix, '@' ) . '(\w+)' . preg_quote( $this->suffix, '@' ) . ')',
            2
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
     */
    public function setRequest( SimplifiedRequest $request )
    {
        if ( !$this->element )
        {
            $this->setMatchElement( $request->pathinfo );
        }

        parent::setRequest( $request );
    }

    /**
     * Analyses $uri and removes the siteaccess part, if needed.
     *
     * @param string $uri The original URI
     *
     * @return string The modified URI
     */
    public function analyseURI( $uri )
    {
        $uri = '/' . ltrim( $uri, '/' );
        return preg_replace( "@$this->regex@", '', $uri );
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
        $linkUri = '/' . ltrim( $linkUri, '/' );
        $siteAccessUri = "/$this->prefix" . $this->match() . $this->suffix;
        return $siteAccessUri . $linkUri;
    }
}
