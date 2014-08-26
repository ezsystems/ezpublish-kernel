<?php
/**
 * File containing the Guzzle4Driver class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;

use GuzzleHttp\Client;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Exception\RequestException;

class Guzzle4Driver extends RestClient
{
    /**
     * @var \GuzzleHttp\Message\MessageFactoryInterface
     */
    protected $request;

    /**
     * @var \GuzzleHttp\Message\MessageFactoryInterface
     */
    protected $response;

    public function getResponseBody()
    {
        return $this->response->getBody();
    }

    public function getResponseHeaders()
    {
        $headers = array();
        foreach ( (array)$this->response->getHeaders() as $header => $headerObject )
        {
            // only getting the first of the array, most of the cases this will
            // work as expected
            $headers[strtolower( $header )] = is_array( $headerObject )?
                $headerObject[0]:
                $headerObject;
        }

        return $headers;
    }

    public function getResponseStatusCode()
    {
        return $this->response->getStatusCode();
    }

    public function getResponseStatusMessage()
    {
        return $this->response->getReasonPhrase();
    }

    protected function makeRequestBody()
    {
        return Stream::factory( $this->body );
    }

    public function sendRequest()
    {
        $client = new Client();

        $request = $client->createRequest(
            $this->requestType,
            $this->host . $this->resource
        );

        // set headers
        foreach ( $this->headers as $header => $value )
        {
            $request->setHeader( $header, $value );
        }

        // set body
        if ( !empty( $this->body ) )
        {
            $request->setBody( $this->makeRequestBody() );
        }

        $this->request = $request;

        try
        {
            // finally send the request
            $this->response = $client->send( $request );
        }
        // if the response is an 40x or a 50x then it will throw an exception
        // we catch and get the response stored on the request object
        catch ( RequestException $e )
        {
            $this->response = $e->getResponse();
        }
    }
}
