<?php

/**
 * File containing the RestConfigurationProvider class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\CorsOptions;

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
    public function __construct(RequestMatcherInterface $requestMatcher)
    {
        $this->requestMatcher = $requestMatcher;
    }

    /**
     * Returns allowed CORS methods for a REST route.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function getOptions(Request $request)
    {
        $return = [];
        if ($request->attributes->has('is_rest_request') && $request->attributes->get('is_rest_request') === true) {
            $return['allow_methods'] = $this->getAllowedMethods($request->getPathInfo());
        }

        return $return;
    }

    protected function getAllowedMethods($uri)
    {
        try {
            $route = $this->requestMatcher->matchRequest(
                Request::create($uri, 'OPTIONS')
            );
            if (isset($route['allowedMethods'])) {
                return explode(',', $route['allowedMethods']);
            }
        } catch (ResourceNotFoundException $e) {
            // the provider doesn't care about a not found
        } catch (MethodNotAllowedException $e) {
            // neither does it care about a method not allowed
        }

        return [];
    }
}
