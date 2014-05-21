<?php
/**
 * File containing the GuzzleClient class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;

use Guzzle\Http\Client;

class GuzzleClient extends RestClient
{
    /**
     * @var \Guzzle\Http\Message\Request
     */
    protected $request;

    /**
     * @var \Guzzle\Http\Message\Response
     */
    protected $response;

    public function getResponseBody()
    {
        $bodyObject = $this->response->getBody();

        $bodyObject->rewind();

        $length = $bodyObject->getContentLength();
        if ( $length === false || $length <= 0 )
        {
            return "";
        }

        return $bodyObject->read( $length );
    }

    public function getResponseHeaders()
    {
        $headers = array();
        foreach ( $this->response->getHeaders()->getAll() as $header => $headerObject )
        {
            $allHeaderValues = $headerObject->toArray();
            // only getting the first of the array, most of the cases this will
            // work as expected
            $headers[$header] = $allHeaderValues[0];
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

    public function sendRequest()
    {
        $client = new Client();

        // make a request object
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
            $request->setBody( $this->body );
        }

        $this->request = $request;

        try
        {
            // finally send the request
            $this->response = $request->send();
        }
        // if the response is an 40x or a 50x then it will throw an exception
        // we catch and get the response stored on the request object
        catch ( \Exception $e )
        {
            $this->response = $this->request->getResponse();
        }
    }
}
