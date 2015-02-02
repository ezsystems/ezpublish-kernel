<?php
/**
 * File containing the HttpClient interface
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway;

/**
 * Interface for Http Client implementations
 */
interface HttpClient
{
    /**
     * Execute a HTTP request to the remote server
     *
     * Returns the result from the remote server.
     *
     * @param string $method
     * @param string $path
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway\Message $message
     *
     * @return \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway\Message
     */
    public function request( $method, $path, Message $message = null );
}
