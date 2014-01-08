<?php
/**
 * File containing the RestConfigurationProvider class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\CorsOptions;

use Exception;
use Nelmio\CorsBundle\Options\ProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * REST Cors Options provider.
 *
 * Uses the REST OPTIONS routes allowedMethods attribute to provide the list of methods allowed for an URI.
 */
class RestProvider implements ProviderInterface
{
    /** @var RequestMatcherInterface */
    protected $requestMatcher;

    /**
     * @param RequestMatcherInterface $requestMatcher
     */
    public function __construct( RequestMatcherInterface $requestMatcher )
    {
        $this->requestMatcher = $requestMatcher;
    }

    /**
     * Returns allowed CORS methods for a REST route
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function getOptions( Request $request )
    {
        $return = array();
        if ( $request->attributes->has( 'is_rest_request' ) )
        {
            $return['allow_methods'] = $this->getAllowedMethods( $request->getPathInfo() );
        }
        return $return;
    }

    protected function getAllowedMethods( $uri )
    {
        try
        {
            $route = $this->requestMatcher->matchRequest(
                Request::create( $uri, 'OPTIONS' )
            );
            if ( isset( $route['allowedMethods'] ) )
            {
                return explode( ',', $route['allowedMethods'] );
            }
        }
        catch ( ResourceNotFoundException $e )
        {
            // the provider doesn't care about a not found
        }
        catch ( MethodNotAllowedException $e )
        {
            // neither does it care about a method not allowed
        }

        return array();
    }
}
