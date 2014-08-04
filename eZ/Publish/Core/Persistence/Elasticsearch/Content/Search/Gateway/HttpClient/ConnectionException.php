<?php
/**
 * File containing the ConnectionException class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Gateway\HttpClient;

use RuntimeException;

/**
 * HTTPClient connection exception
 */
class ConnectionException extends RuntimeException
{
    public function __construct( $server, $path, $method )
    {
        parent::__construct(
            "Could not connect to server $server."
        );
    }
}
