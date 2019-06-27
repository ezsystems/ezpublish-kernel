<?php

/**
 * File containing the GuzzleDriver class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Header;
use Guzzle\Http\Exception\BadResponseException;

class GuzzleDriver implements DriverInterface
{
    use DriverHelper;

    /** @var bool */
    protected $sent = false;

    /** @var \Guzzle\Http\Client */
    protected $client;

    /** @var \Guzzle\Http\Message\Request */
    protected $request;

    /** @var \Guzzle\Http\Message\Response */
    protected $response;

    /** @var string */
    protected $host;

    /** @var string */
    protected $resource;

    /** @var string */
    protected $method;

    /** @var array Associative array with 'header' => 'value' */
    protected $headers;

    /** @var string */
    protected $body;

    /**
     * Instanciate a client.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Get reponse.
     *
     * @return \Guzzle\Http\Message\Response
     *
     * @throws \RuntimeException If request hasn't been send already
     */
    protected function getResponse()
    {
        if ($this->sent) {
            return $this->response;
        }

        throw new \RuntimeException("Attempt to get response data when request hasn't been sent yet");
    }

    /**
     * Send the request.
     */
    public function send()
    {
        // make a request object
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
        } catch (BadResponseException $e) {
            // if the response is an 40x or a 50x then it will throw an exception
            // we catch and get the response stored on the request object
            $this->response = $this->request->getResponse();
        }

        $this->sent = true;
    }

    /**
     * Set request host.
     *
     * @param string $host
     */
    public function setHost($host)
    {
        if (substr($host, -1) === '/') {
            $host = substr($host, 0, strlen($host) - 1);
        }

        $this->host = $host;
    }

    /**
     * Set request resource url.
     *
     * @param string $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Set request method.
     *
     * @param string $method Can be GET, POST, PATCH, ...
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Get response status code.
     *
     * @return string
     *
     * @throws \RuntimeException If request hasn't been send already
     */
    public function getStatusCode()
    {
        return $this->getResponse()->getStatusCode();
    }

    /**
     * Get response status message.
     *
     * @return string
     *
     * @throws \RuntimeException If request hasn't been send already
     */
    public function getStatusMessage()
    {
        return $this->getResponse()->getReasonPhrase();
    }

    /**
     * Set request header.
     *
     * @param string $header Header to be set
     */
    public function setHeader($header, $value)
    {
        if (is_array($value)) {
            $value = implode(';', $value);
        }

        $this->headers[$header] = $value;
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
        foreach ($this->response->getHeaders()->getAll() as $header => $headerObject) {
            $allHeaderValues = $headerObject->toArray();
            $headers[strtolower($header)] = implode(';', $allHeaderValues);
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
        $bodyObject = $this->response->getBody();

        $bodyObject->rewind();

        $length = $bodyObject->getContentLength();
        if ($length === false || $length <= 0) {
            return '';
        }

        return $bodyObject->read($length);
    }

    /**
     * Set request body.
     *
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }
}
