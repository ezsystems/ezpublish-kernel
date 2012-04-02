<?php
/**
 * File containing the HttpClient Response struct
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client\HttpClient;

/**
 * Response base struct
 */
class Response
{
    /**
     * HTTP response status
     *
     * @var int
     */
    public $status;

    /**
     * HTTP response headers
     *
     * @var array
     */
    public $headers;

    /**
     * HTTP response body
     *
     * @var string
     */
    public $body;

    /**
     * Cosntruct
     *
     * @param int $status
     * @param array $headers
     * @param string $body
     * @return void
     */
    public function __construct( $status, array $headers, $body )
    {
        $this->status  = $status;
        $this->headers = $headers;
        $this->body    = $body;
    }
}

