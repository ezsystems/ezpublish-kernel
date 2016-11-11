<?php

/**
 * File containing the DriverInterface for RestDrivers.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;

/**
 * DriverInterface has the definition of the methods needed for our REST tests
 * so Drivers need to implement this interface for a seamless interaction.
 *
 * Notice: Setters are for request and Getters for response since no assertion is
 *      done on requests, and no set is done onto responses
 */
interface DriverInterface
{
    /**
     * authentication types.
     */
    const AUTH_TYPE_BASIC = 'BASIC';

    /**
     * Send the request.
     */
    public function send();

    /**
     * Set request host.
     *
     * @param string $host
     */
    public function setHost($host);

    /**
     * Set request resource url.
     *
     * @param string $resource
     */
    public function setResource($resource);

    /**
     * Set request method.
     *
     * @param string $method Can be GET, POST, PATCH, ...
     */
    public function setMethod($method);

    /**
     * Get response status code.
     *
     * @return string
     *
     * @throws \RuntimeException If request hasn't been send already
     */
    public function getStatusCode();

    /**
     * Get response status message.
     *
     * @return string
     *
     * @throws \RuntimeException If request hasn't been send already
     */
    public function getStatusMessage();

    /**
     * Get response header.
     *
     * @param string $header Header to fetch
     *
     * @return string Header value, or a list if its more than one
     *
     * @throws \RuntimeException If request hasn't been send already
     */
    public function getHeader($header);

    /**
     * Set request header.
     *
     * @param string $header Header to be set
     */
    public function setHeader($header, $value);

    /**
     * Get all response headers.
     *
     * @return array Associative array with $header => $value (value can be an array if it hasn't a single value)
     *
     * @throws \RuntimeException If request hasn't been send already
     */
    public function getHeaders();

    /**
     * Set request headers.
     *
     * @param array $headers Associative array with $header => $value (value can be an array if it hasn't a single value)
     */
    public function setHeaders($headers);

    /**
     * Get response body.
     *
     * @return string
     *
     * @throws \RuntimeException If request hasn't been send already
     */
    public function getBody();

    /**
     * Set request body.
     *
     * @param string $body
     */
    public function setBody($body);

    /**
     * Set authentication.
     *
     * @param string $user
     * @param string $password
     * @param string $type Authentication type
     */
    public function setAuthentication($user, $password, $type = self::AUTH_TYPE_BASIC);
}
