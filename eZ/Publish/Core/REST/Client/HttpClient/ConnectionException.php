<?php

/**
 * File containing the ConnectionException class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\HttpClient;

use RuntimeException;

/**
 * HTTPClient connection exception.
 */
class ConnectionException extends RuntimeException
{
    /**
     * Constructor.
     *
     * @param string $server
     * @param string $path
     * @param string $method
     */
    public function __construct($server, $path, $method)
    {
        parent::__construct(
            "Could not connect to server '$server' and retrieve '$path' with '$method'."
        );
    }
}
