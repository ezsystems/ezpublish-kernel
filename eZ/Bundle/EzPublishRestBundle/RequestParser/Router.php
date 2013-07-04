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
use Symfony\Component\Routing\RouterInterface;
use eZ\Publish\Core\REST\Common\Exceptions\InvalidArgumentException;

/**
 * Router based request parser
 */
class Router implements RequestParser
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    private $restRoutesPrefix;

    public function __construct( $restRoutesPrefix, RouterInterface $router )
    {
        $this->router = $router;
        $this->restRoutesPrefix = $restRoutesPrefix;
    }

    /**
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException If no match was found
     */
    public function parse( $url )
    {
        // we create a request with a new context
        $request = Request::create(
            $this->restRoutesPrefix . $url, "GET"
        );

        $originalContext = $this->router->getContext();
        $context = clone $originalContext;
        $context->fromRequest( $request );
        $this->router->setContext( $context );

        $matchResult = $this->router->matchRequest( $request );

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
        try
        {
            $parsingResult = $this->parse( $href );
        }
        catch ( \Symfony\Component\Routing\Exception\ResourceNotFoundException $e )
        {
            // Note: this probably won't occur in real life because of the legacy matcher
            throw new InvalidArgumentException( "No route matched '$href''" );
        }

        if ( $parsingResult['_route'] === 'ez_legacy' )
            throw new InvalidArgumentException( "No route matched '$href'" );

        if ( !isset( $parsingResult[$attribute] ) )
            throw new InvalidArgumentException( "No such attribute '$attribute' in route matched from $href\n" . print_r( $parsingResult, true ) );

        return $parsingResult[$attribute];
    }
}
