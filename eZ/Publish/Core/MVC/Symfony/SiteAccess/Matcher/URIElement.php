<?php
/**
 * File containing the eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher\URIElement class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;

use eZ\Publish\Core\MVC\Symfony\SiteAccess\Matcher;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer;

class URIElement implements Matcher, URILexer
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest
     */
    private $request;

    /**
     * Number of elements to take into account.
     *
     * @var int
     */
    private $elementNumber;

    /**
     * Constructor.
     *
     * @param int $elementNumber Number of elements to take into account.
     */
    public function __construct( $elementNumber )
    {
        $this->elementNumber = (int)$elementNumber;
    }

    /**
     * Returns matching Siteaccess.
     *
     * @return string|false Siteaccess matched or false.
     */
    public function match()
    {
        try
        {
            return implode( "_", $this->getURIElements() );
        }
        catch ( \LogicException $e )
        {
            return false;
        }
    }

    /**
     * Returns URI elements as an array.
     * @throws \LogicException
     *
     * @return array
     */
    protected function getURIElements()
    {
        $elements = array_slice(
            explode( "/", $this->request->pathinfo ),
            1,
            $this->elementNumber
        );

        // If one of the elements is empty, we do not match.
        foreach ( $elements as $element )
        {
            if ( $element === "" )
                throw new \LogicException( 'One of the URI elements was empty' );
        }

        if ( count( $elements ) !== $this->elementNumber )
            throw new \LogicException( 'The number of provided elements to consider is different than the number of elements found in the URI' );

        return $elements;
    }

    public function getName()
    {
        return 'uri:element';
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
        $this->request = $request;
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
        $uriElements = '/' . implode( '/', $this->getURIElements() );
        if ( $uri == $uriElements )
        {
            $uri = '';
        }
        else if ( strpos( $uri, $uriElements ) === 0 )
        {
            sscanf( $uri, "$uriElements%s", $uri );
        }
        return $uri;
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
        $uriElements = implode( '/', $this->getURIElements() );
        return "/{$uriElements}{$linkUri}";
    }
}
