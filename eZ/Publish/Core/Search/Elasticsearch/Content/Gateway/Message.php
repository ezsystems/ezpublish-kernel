<?php

/**
 * File containing the Message class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\Gateway;

/**
 * Simple response struct.
 */
class Message
{
    /**
     * Response headers.
     *
     * @var array
     */
    public $headers;

    /**
     * Response body.
     *
     * @var string
     */
    public $body;

    /**
     * Construct from headers and body.
     *
     * @param array $headers
     * @param string $body
     */
    public function __construct(array $headers = [], $body = '')
    {
        $this->headers = $headers;
        $this->body = $body;
    }
}
