<?php
/**
 * File containing the Symfony RequestParser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\RequestParser;

use eZ\Publish\Core\REST\Common\RequestParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use eZ\Publish\Core\REST\Common\Exceptions\InvalidArgumentException;

/**
 * Router based request parser
 */
class Router implements RequestParser
{
    /**
     * @var \Symfony\Cmf\Component\Routing\ChainRouter
     */
    private $router;

    /**
     * The configured  prefix for REST routes
     * @var string
     */
    private $restRoutesPrefix;

    public function __construct( $restRoutesPrefix, RouterInterface $router )
    {
        $this->router = $router;
        $this->restRoutesPrefix = $restRoutesPrefix;
    }

    /**
     * @throws ResourceNotFoundException If no match was found
     */
    public function parse( $url )
    {
        // we create a request with a new context in order to match $url to a route and get its properties
        $request = Request::create( $url, "GET" );
        $originalContext = $this->router->getContext();
        $context = clone $originalContext;
        $context->fromRequest( $request );
        $this->router->setContext( $context );

        try
        {
            $matchResult = $this->router->matchRequest( $request );
        }
        // Note: this probably won't occur in real life because of the legacy matcher
        catch ( ResourceNotFoundException $e )
        {
            $this->router->setContext( $originalContext );
            throw new InvalidArgumentException( "No route matched '$url'" );
        }

        if ( !$this->matchesRestRequest( $matchResult ) )
        {
            $this->router->setContext( $originalContext );
            throw new InvalidArgumentException( "No route matched '$url'" );
        }

        $this->router->setContext( $originalContext );

        return $matchResult;
    }

    public function generate( $type, array $values = array() )
    {
        return $this->router->generate(
            $type, $values
        );
    }

    /**
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If $attribute wasn't found in the match
     */
    public function parseHref( $href, $attribute )
    {
        $parsingResult = $this->parse( $href );

        if ( !isset( $parsingResult[$attribute] ) )
        {
            throw new InvalidArgumentException( "No such attribute '$attribute' in route matched from $href" );
        }

        return $parsingResult[$attribute];
    }

    /**
     * Checks if a router match response matches a REST resource
     * @param array $match Match array returned by Router::match() / Router::matchRequest()
     * @throws \eZ\Publish\Core\REST\Common\Exceptions\InvalidArgumentException if the \$match isn't valid
     * @return bool
     */
    private function matchesRestRequest( array $match )
    {
        if ( !isset( $match['_route'] ) )
            throw new InvalidArgumentException( "Invalid \$match parameter, no _route key" );

        return ( strpos( $match['_route'], 'ezpublish_rest_' ) === 0 );
    }
}
