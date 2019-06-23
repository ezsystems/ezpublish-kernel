<?php

/**
 * File containing the Guzzle4Driver class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;

use GuzzleHttp\Client;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Exception\RequestException;

class Guzzle4Driver extends GuzzleDriver
{
    /**
     * Initialize client.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Get all response headers.
     *
     * @return array Associative array with $header => $value (value can be an array if it hasn't a single value)
     *
     * @throws \RuntimeException If request hasn't been send already
     */
    public function getHeaders()
    {
        $headers = [];
        foreach ((array)$this->getResponse()->getHeaders() as $header => $headerObject) {
            $headers[strtolower($header)] = implode(';', $headerObject);
        }

        return $headers;
    }

    /**
     * Get response body.
     *
     * @return string
     *
     * @throws \RuntimeException If request hasn't been send already
     */
    public function getBody()
    {
        return (string)$this->getResponse()->getBody();
    }

    /**
     * Set request body.
     *
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = empty($body) ?
            null :
            Stream::factory($body);
    }

    /**
     * Send the request.
     */
    public function send()
    {
        $this->request = $this->client->createRequest(
            $this->method,
            $this->host . $this->resource
        );

        // set headers
        foreach ($this->headers as $header => $value) {
            $this->request->setHeader($header, $value);
        }

        // set body
        if (!empty($this->body)) {
            $this->request->setBody($this->body);
        }

        try {
            // finally send the request
            $this->response = $this->client->send($this->request);
        } catch (RequestException $e) {
            // if the response is an 40x or a 50x then it will throw an exception
            // we catch and get the response stored on the request object
            $this->response = $e->getResponse();
        }

        $this->sent = true;
    }
}
