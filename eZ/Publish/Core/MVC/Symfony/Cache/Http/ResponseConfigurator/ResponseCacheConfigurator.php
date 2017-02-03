<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator;

use Symfony\Component\HttpFoundation\Response;

/**
 * Configures caching options of an HTTP Response.
 */
interface ResponseCacheConfigurator
{
    /**
     * Enables cache on a Response.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return ResponseCacheConfigurator
     */
    public function enableCache(Response $response);

    /**
     * Sets the shared-max-age property of a Response if it is not already set.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return ResponseCacheConfigurator
     */
    public function setSharedMaxAge(Response $response);

    /**
     * Adds $tags to the response's cache tags header.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param string|array $tags Single tag, or array of tags
     *
     * @return ResponseCacheConfigurator
     */
    public function addTags(Response $response, $tags);
}
