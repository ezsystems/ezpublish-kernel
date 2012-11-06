<?php
/**
 * File containing the Http MultiCurlClient class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http;

use Buzz\Client\MultiCurl;

class MultiCurlClient extends MultiCurl
{
    /**
     * Timeout for the curl request, in ms.
     *
     * @var int
     */
    static protected $timeoutMs;

    public function __construct( $timeoutMs )
    {
        static::$timeoutMs = $timeoutMs;
        parent::__construct();
    }

    static protected function createCurlHandle()
    {
        $curl = parent::createCurlHandle();

        curl_setopt( $curl, CURLOPT_TIMEOUT_MS, static::$timeoutMs );
        // PURGE requests don't need any body
        curl_setopt( $curl, CURLOPT_NOBODY, true );

        return $curl;
    }
}
