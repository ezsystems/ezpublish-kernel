<?php
/**
 * File containing the DriverHelper trait for RestDrivers.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;

/**
 * DriverHelper has some generic actions
 */
trait DriverHelper
{
    /**
     * Make/Crypt the authentication value
     *
     * @param string $user
     * @param string $password
     * @param string $type The type of authentication
     *
     * @return string Authentication value
     *
     * @throws \UnexpectedValueException If the $type doesn't exists
     */
    protected function makeAuthentication( $username, $password, $type )
    {
        switch ( $type )
        {
            case self::AUTH_TYPE_BASIC:
                return "Basic " . base64_encode( "$username:$password" );

            default:
                throw new \UnexpectedValueException( "Authentication '$authType' invalid or not implemented yet" );
        }
    }

    /**
     * Set authentication
     *
     * @param string $user
     * @param string $password
     * @param string $type Authentication type
     *
     * @return void
     */
    public function setAuthentication( $user, $password, $type = self::AUTH_TYPE_BASIC )
    {
        $this->setHeader(
            'Authorization',
            $this->makeAuthentication( $user, $password, $type )
        );
    }

    /**
     * Get response header
     *
     * @param string $header Header to fetch
     *
     * @return string Header value, or a list if its more than one
     *
     * @throws \RuntimeException If request hasn't been send already
     */
    public function getHeader( $header )
    {
        $header = strtolower( $header );
        $headers = $this->getHeaders();

        return empty( $headers[$header] ) ? null : $headers[$header];
    }

    /**
     * Set request headers
     *
     * @param array $headers Associative array with $header => $value (value can be an array if it hasn't a single value)
     *
     * @return void
     */
    public function setHeaders( $headers )
    {
        foreach ( $headers as $header => $value )
        {
            $this->setHeader( $header, $value );
        }
    }
}
