<?php

/**
 * File containing the BuzzDriver class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;

use Buzz\Message\Request;
use Buzz\Message\Response;
use Buzz\Client\Curl;

class BuzzDriver implements DriverInterface
{
    use DriverHelper;

    /**
     * @var bool
     */
    private $sent = false;

    /**
     * @var \Buzz\Message\Request
     */
    private $request;

    /**
     * @var \Buzz\Message\Response
     */
    private $response;

    /**
     * Initialize the request and response.
     */
    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    /**
     * Get reponse.
     *
     * @return \Buzz\Message\Response
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
     * Get request.
     *
     * @return \Buzz\Message\Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Send the request.
     */
    public function send()
    {
        // prepare client
        $curl = new Curl();
        $curl->setTimeout(10);
        $curl->send(
            $this->request,
            $this->response
        );

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

        $this->getRequest()->setHost($host);
    }

    /**
     * Set request resource url.
     *
     * @param string $resource
     */
    public function setResource($resource)
    {
        $this->getRequest()->setResource($resource);
    }

    /**
     * Set request method.
     *
     * @param string $method Can be GET, POST, PATCH, ...
     */
    public function setMethod($method)
    {
        $this->getRequest()->setMethod($method);
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

        // Buzz can only add/append header, so we need to (re-)set all headers
        $headers = $this->unFormatHeaders($this->getRequest()->getHeaders());
        $headers[$header] = $value;
        $this->getRequest()->setHeaders($headers);
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
        return $this->unFormatHeaders($this->response->getHeaders());
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
        return $this->getResponse()->getContent();
    }

    /**
     * Set request body.
     *
     * @param string $body
     */
    public function setBody($body)
    {
        $this->getRequest()->setContent($body);
    }

    /**
     * Converts the buzz headers attributes into single lines.
     *
     * @param array $headers All headers
     *
     * @return array
     */
    protected function unFormatHeaders(array $headers)
    {
        $headersInAssociativeArray = array();
        foreach ($headers as $header) {
            $colonPosition = strpos($header, ':');

            // if no ':' is found than add all header to array
            if ($colonPosition === false) {
                $headersInAssociativeArray[] = $header;
            } else {
                $key = strtolower(trim(substr($header, 0, $colonPosition)));
                $value = trim(substr($header, $colonPosition + 1, strlen($header)));
                $headersInAssociativeArray[$key] = $value;
            }
        }

        return $headersInAssociativeArray;
    }
}
