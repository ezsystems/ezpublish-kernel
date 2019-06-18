<?php

/**
 * File containing the Message class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common;

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
     * HTTP status code.
     *
     * @var int
     */
    public $statusCode;

    /**
     * Construct from headers and body.
     *
     * @param array $headers
     * @param string $body
     * @param int $statusCode
     */
    public function __construct(array $headers = [], $body = '', $statusCode = 200)
    {
        $this->headers = $headers;
        $this->body = $body;
        $this->statusCode = $statusCode;
    }
}
