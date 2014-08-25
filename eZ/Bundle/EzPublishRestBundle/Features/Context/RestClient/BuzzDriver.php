<?php
/**
 * File containing the BuzzDriver class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;

use Buzz\Message\Request;
use Buzz\Message\Response;
use Buzz\Client\Curl;

class BuzzDriver extends RestClient
{
    /**
     * @var \Buzz\Message\Request
     */
    protected $request;

    /**
     * @var \Buzz\Message\Response
     */
    protected $response;

    public function getResponseHeaders()
    {
        return $this->unFormatHeaders( $this->response->getHeaders() );
    }

    public function getResponseBody()
    {
        return $this->response->getContent();
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
        // new Request
        $request = new Request(
            $this->requestType,
            $this->resource,
            $this->host
        );

        // add headers
        $request->addHeaders( $this->headers );

        // add body
        $request->setContent( $this->body );

        // prepare client
        $response = new Response();
        $curl = new Curl();
        $curl->send(
            $request,
            $response
        );

        $this->request = $request;
        $this->response = $response;
    }

    public function getCompleteResponse()
    {
        return $this->response;
    }

    public function getCompleteRequest()
    {
        return $this->request;
    }
}
