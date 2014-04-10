<?php
/**
 * File containing the RestClientContext class.
 *
 * This class Rest Client for BDD testing
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;

use eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient\RestClient;
use Guzzle\Http\Client;

/**
 * Guzzle REST Client.
 */
class GuzzleClient extends RestClient
{
    /**
     * @var Guzzle\Http\Message\Request
     */
    protected $request;

    /**
     * @var Guzzle\Http\Message\Response
     */
    protected $response;

    public function getResponseBody()
    {
        $bodyObject = $this->response->getBody();

        $bodyObject->rewind();

        return $bodyObject->read( $bodyObject->getContentLength() );
    }

    public function getResponseHeaders()
    {
        $headers = array();
        foreach( $this->response->getHeaders()->getAll() as $header => $headerObject )
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
        foreach( $this->headers as $header => $value )
        {
            $request->setHeader( $header, $value );
        }

        // set body
        $request->setBody( $this->body );

        $this->request = $request;

        try
        {
            // finaly send the request
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
