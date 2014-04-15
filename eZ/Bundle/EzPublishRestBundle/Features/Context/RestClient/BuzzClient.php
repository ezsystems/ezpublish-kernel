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
use Buzz\Message\Request;
use Buzz\Message\Response;
use Buzz\Client\Curl;

/**
 * Buzz Rest Client.
 */
class BuzzClient extends RestClient
{
    /**
     * @var Buzz\Message\Request
     */
    protected $request;

    /**
     * @var Buzz\Message\Response
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
        // new Resquest
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
