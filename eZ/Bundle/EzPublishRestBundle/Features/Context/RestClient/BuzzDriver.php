<?php

/**
 * File containing the BuzzDriver class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;

use Nyholm\Psr7\Request;
use Buzz\Client\Curl;

class BuzzDriver implements DriverInterface
{
    use DriverHelper;

    /** @var \Psr\Http\Message\ResponseInterface */
    private $response = null;

    /**
     * Host used to prepare Request URI.
     *
     * @var string
     */
    private $host = null;

    /**
     * Resource path used to prepare Request URI.
     *
     * @var string
     */
    private $resource = '';

    /**
     * HTTP method used to prepare Request.
     *
     * @var string
     */
    private $method = null;

    /**
     * Request headers.
     *
     * @var array
     */
    private $headers = [];

    /**
     * Request message body.
     *
     * @var string
     */
    private $body = null;

    /**
     * Get response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \RuntimeException If request hasn't been send already
     */
    protected function getResponse()
    {
        if (null !== $this->response) {
            return $this->response;
        }

        throw new \RuntimeException("Attempt to get response data when request hasn't been sent yet");
    }

    /**
     * Prepare and get request.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function getRequest()
    {
        if (empty($this->method) || empty($this->host)) {
            throw new \RuntimeException('Attempted to get unspecified Request');
        }

        return new Request(
            $this->method,
            $this->host . $this->resource,
            $this->headers,
            $this->body
        );
    }

    /**
     * Send the request.
     */
    public function send()
    {
        // prepare client
        $curl = new Curl(
            [
                'timeout' => 10,
            ]
        );
        $this->response = null;
        $this->response = $curl->sendRequest($this->getRequest());
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
        if (in_array(strtolower($method), ['publish', 'patch', 'move', 'swap'])) {
            $this->headers['X-HTTP-Method-Override'] = $method;
            $this->method = 'POST';
        } else {
            $this->method = strtoupper($method);
        }
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
     * @param mixed $value
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
        return $this->response->getHeaders();
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
        $bodyStream = $this->getResponse()->getBody();
        $contents = $bodyStream->getContents();
        $bodyStream->rewind();

        return $contents;
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
