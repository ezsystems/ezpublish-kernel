<?php
/**
 * File containing the ConnectionException class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\Gateway\HttpClient;

/**
 * HTTPClient connection exception
 */
class ConnectionException extends \RuntimeException
{
    public function __construct( $server, $path, $method )
    {
        parent::__construct(
            "Could not connect to server $server."
        );
    }
}
