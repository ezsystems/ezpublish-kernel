<?php

/**
 * File containing the HttpClient interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\Gateway;

/**
 * Interface for Http Client implementations.
 */
interface HttpClient
{
    /**
     * Execute a HTTP request to the remote server.
     *
     * Returns the result from the remote server.
     *
     * @param string $method
     * @param \eZ\Publish\Core\Search\Solr\Content\Gateway\Endpoint $endpoint
     * @param string $path
     * @param \eZ\Publish\Core\Search\Solr\Content\Gateway\Message $message
     *
     * @return \eZ\Publish\Core\Search\Solr\Content\Gateway\Message
     */
    public function request($method, Endpoint $endpoint, $path, Message $message = null);
}
