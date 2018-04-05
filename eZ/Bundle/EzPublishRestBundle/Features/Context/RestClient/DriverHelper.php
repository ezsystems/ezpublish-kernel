<?php

/**
 * File containing the DriverHelper trait for RestDrivers.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;

/**
 * DriverHelper has some generic actions.
 */
trait DriverHelper
{
    /**
     * Make/Crypt the authentication value.
     *
     * @param string $username
     * @param string $password
     * @param string $type The type of authentication
     *
     * @return string Authentication value
     */
    protected function makeAuthentication($username, $password, $type)
    {
        switch ($type) {
            case self::AUTH_TYPE_BASIC:
                return 'Basic ' . base64_encode("$username:$password");

            default:
                throw new \UnexpectedValueException("Authentication '{$type}' invalid or not implemented yet");
        }
    }

    /**
     * Set authentication.
     *
     * @param string $user
     * @param string $password
     * @param string $type Authentication type
     */
    public function setAuthentication($user, $password, $type = self::AUTH_TYPE_BASIC)
    {
        $this->setHeader(
            'Authorization',
            $this->makeAuthentication($user, $password, $type)
        );
    }

    /**
     * Get response header.
     *
     * @param string $header Header to fetch
     *
     * @return string Header value, or a list if its more than one
     *
     * @throws \RuntimeException If request hasn't been send already
     */
    public function getHeader($header)
    {
        $response = $this->getResponse();

        return $response->hasHeader($header) ? $response->getHeader($header)[0] : null;
    }

    /**
     * Set request headers.
     *
     * @param array $headers Associative array with $header => $value (value can be an array if it hasn't a single value)
     */
    public function setHeaders($headers)
    {
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value);
        }
    }
}
