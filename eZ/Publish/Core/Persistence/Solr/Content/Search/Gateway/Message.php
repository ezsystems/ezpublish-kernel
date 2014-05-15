<?php
/**
 * File containing the Response class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway;

/**
 * Simple response struct
 */
class Message
{
    /**
     * Response headers
     *
     * @var array
     */
    public $headers;

    /**
     * Response body
     *
     * @var string
     */
    public $body;

    /**
     * Construct from headers and body
     *
     * @param array $headers
     * @param string $body
     *
     * @return void
     */
    public function __construct( array $headers = array(), $body = '' )
    {
        $this->headers = $headers;
        $this->body    = $body;
    }
}

