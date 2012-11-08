<?php
/**
 * File containing the Http Curl client class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use Buzz\Client\Curl as BaseCurl,
    Buzz\Message\RequestInterface,
    Buzz\Message\MessageInterface;

class Curl extends BaseCurl
{
    public function __construct( $timeout )
    {
        $this->setTimeout( $timeout );
        $this->setOption( CURLOPT_NOBODY, true );
    }

    public function send( RequestInterface $request, MessageInterface $response, array $options = array() )
    {
        try
        {
            parent::send( $request, $response, $options );
        }
        catch ( \RuntimeException $e )
        {
            // Catch but do not do anything as we consider the request to be ~ asynchronous
        }
    }
}
