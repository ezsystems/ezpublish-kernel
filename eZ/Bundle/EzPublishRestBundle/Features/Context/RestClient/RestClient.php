<?php
/**
 * File containing the RestInterface class.
 *
 * This class contains the interface for the REST implementations
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

abstract class RestClient
{
    // default host
    const DEFAULT_HOST = 'localhost';

    // prefix
    const CONTENT_HEADER_PREFIX = 'application/vnd.ez.api.';
    const RESOURCE_PREFIX = 'api/ezp/v2';

    // authentication types
    const AUTH_TYPE_BASIC = 'BASIC';
    const AUTH_TYPE_OAUTH = 'OAUTH';

    /**
     * @var string This will have the host to simplify the intended URL's
     */
    protected $host;

    /**
     * @var string This is the url that should NOT contain any host and/or prefix
     */
    protected $resource;

    /**
     * @var string Type of request (most common; GET, POST, PATCH, DELETE, ...)
     */
    protected $requestType;

    /**
     * @var string The type for the content (xml or json)
     */
    protected $bodyType;

    /**
     * @var array Array of headers to be sent on the request
     *
     * <code>
     *  return array(
     *      'header1' => 'value1',
     *      'header2' => 'value2',
     *      ...
     *      'headerN' => 'valueN',
     *  );
     * </code>
     */
    protected $headers;

    /**
     * @var string Body/contents of the request
     */
    protected $body;

    /**
     * @var array Array of headers from the response
     *
     * @see RestClientInterface::$headers
     */
    protected $responseHeaders;

    /**
     * @var string All response body/content
     */
    protected $responseBody;

    /**
     * @var int Response code (200, ..., 400, 404, ...)
     */
    protected $responseStatusCode;

    /**
     * @var string Complement to response code ( 'ok', 'created', 'not found', ...)
     */
    protected $responseStatusMessage;

    /**
     * @param string $host Host with or without prefix to where the request should be sent
     * @param string $resource Resource URL to send the request
     * @param string $resourcePrefix The prefix for REST call
     * @param string $requestType Type for the request (POST, GET, PUT, ...)
     * @param string $bodyType Type for the body/content (XML or JSON.)
     */
    public function __construct(
        $host = self::DEFAULT_HOST,
        $resource = null,
        $resourcePrefix = self::RESOURCE_PREFIX,
        $requestType = 'get',
        $bodyType = 'json'
    )
    {
        $this->setHost( $host, $resourcePrefix );
        $this->resource = $resource;
        $this->requestType = strtoupper( $requestType );
        $this->bodyType = $bodyType;
    }

    /**
     * Setter for @var $host
     *
     * @param string $host Host url
     */
    public function setHost( $host, $resourcePrefix = self::RESOURCE_PREFIX )
    {
        // if it doesn't have the protocol add 'http'
        if ( strpos( $host, '://' ) === false )
        {
            $host = 'http://' . $host;
        }

        // remove last '/' from host and first from prefix so that there is no
        // dupplication when concatenating
        if ( substr( $host, -1 ) === '/' )
        {
            $host = substr( $host, 0, strlen( $host ) - 1 );
        }

        if ( substr( $resourcePrefix, 0, 1 ) === '/' )
        {
            $resourcePrefix = substr( $resourcePrefix, 1, strlen( $resourcePrefix ) - 1 );
        }

        $this->host = $host . '/' . $resourcePrefix;
    }

    /**
     * Magic method for the get property value
     *
     * @param string $name Property name
     *
     * @return mixed
     */
    public function __get( $name )
    {
        // for the response properties that needed to be set after the request
        // been sent for the child classes, we need to make the values to be
        // available
        if (
            strpos( $name, 'response' ) === 0
            && !isset( $this->$name )
            && method_exists( $this, 'get' . ucfirst( $name ) )
        )
        {
            $function = 'get' . ucfirst( $name );
            $this->$name = $this->$function();
        }

        return $this->$name;
    }

    /**
     * Setter for @var $headers[$name]
     *
     * @param string $name  Name of the header to be set
     * @param string $value Value for the header
     */
    public function setHeader( $name, $value )
    {
        $this->headers[$name] = $value;
    }

    /**
     * Setter for @var $body
     *
     * @param string $body
     */
    public function setBody( $body )
    {
        $this->body = $body;
    }

    /**
     * Create and header for authentication
     *
     * @param string $username Name of the user for authentication
     * @param string $password Password for the user
     */
    public function setAuthentication( $username, $password, $type = self::AUTH_TYPE_BASIC )
    {
        $this->setHeader(
            'Authorization',
            $this->makeAuthenticationHeader(
                $username,
                $password,
                $type
            )
        );
    }

    /**
     * Setter for @var $resource
     *
     * @param string $resourceUrl Url wihtout host (or prefix)
     */
    public function setResourceUrl( $resourceUrl )
    {
        $this->resource = $resourceUrl;
    }

    /**
     * Setter for @var $requestType
     *
     * @param string $type Type for request (like POST, GET, ....)
     */
    public function setRequestType( $type )
    {
        $this->requestType = strtoupper( $type );
    }

    /**
     * Send the request and store the result on $response
     */
    abstract public function sendRequest();

    /**
     * Get specified response header
     *
     * @param string $header Header to be retrieved
     *
     * @return string The value from the specified header
     */
    public function getResponseHeader( $header )
    {
        if ( empty( $this->responseHeaders ) )
        {
            $this->responseHeaders = $this->getResponseHeaders();
        }

        return empty( $this->responseHeaders[$header] ) ?
            null :
            $this->responseHeaders[$header];
    }

    /**
     * Get all the headers from response
     *
     * @return array Array with the headers of response
     *
     * <code>
     *  return array(
     *      'header1' => 'value1',
     *      'header2' => 'value2',
     *      ...
     *      'headerN' => 'valueN',
     *  );
     * </code>
     */
    abstract public function getResponseHeaders();

    /**
     * Get respose body
     *
     * @return string
     */
    abstract public function getResponseBody();

    /**
     * Get response status code
     *
     * @return string
     */
    abstract public function getResponseStatusCode();

    /**
     * Get response status message
     *
     * @return string
     */
    abstract public function getResponseStatusMessage();

    /**
     * Format headers
     *
     * <code>
     *  return array(
     *      'header1: value1',
     *      'header2: value2',
     *      ...
     *      'headern:valueN'
     *  );
     * </code>
     *
     * @return array
     */
    protected function formatHeaders()
    {
        $formatedHeaders = array();
        foreach ( $this->headers as $header => $value )
        {
            $formatedHeaders = "{$header}: {$value}";
        }

        return $formatedHeaders;
    }

    /**
     * This does exactly the contrary of the formatHeaders() function above
     *
     * @param array $headers All headers
     *
     * @return array
     *
     * @see RestClientInterface::formatHeaders()
     */
    protected function unFormatHeaders( array $headers )
    {
        $headersInAssociativeArray = array();
        foreach ( $headers as $header )
        {
            $colonPosition = strpos( $header, ':' );

            // if no ':' is found than add all header to array
            if ( $colonPosition === false )
            {
                $headersInAssociativeArray[] = $header;
            }
            else
            {
                $key = strtolower( trim( substr( $header, 0, $colonPosition ) ) );
                $value = trim( substr( $header, $colonPosition + 1, strlen( $header ) ) );
                $headersInAssociativeArray[$key] = $value;
            }
        }

        return $headersInAssociativeArray;
    }

    /**
     * Create the authentication header
     *
     * @param string $username Username for the authentication
     * @param string $password Passowrd for the authentication
     * @param string $authtype Authentication type (ex: 'BASIC', 'OAUTH')
     * @return string The value for the 'Authentication' header
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException If the authentication type is not implemented yet
     */
    protected function makeAuthenticationHeader( $username, $password, $authtype )
    {
        if ( empty( $authtype ) )
        {
            return "";
        }

        switch( strtoupper( $authtype ) ){
        case self::AUTH_TYPE_BASIC:
            return "Basic " . base64_encode( "$username:$password" );

        default:
            throw new NotImplementedException( "authentication: '$authtype'" );
        }
    }

    /**
     * Since Content-Type and Accept header have a special construct method they
     * shouldn't be added in the same way has the others
     *
     * @param string $header    This should be used only for 'accept' and 'content-type' headers
     * @param string $object    Content for the header (see self::$contentHeaderTypes)
     * @param string $action    Action for the header (see self::$contentHeaderTypes)
     * @param string $type      Type can be (at this momment) XML or JSON
     */
    public function addSpecialHeader( $header, $object, $action = null, $type = null )
    {
        $this->setHeader( $header, $this->constructSpecialHeader( $object, $action, $type ) );
    }

    /**
     * Create the accept/content-type header the eZ way
     *
     * @param string $object    Content for the header (see self::$contentHeaderTypes)
     * @param string $action    Action for the header (see self::$contentHeaderTypes)
     * @param string $type      Type can be (at this momment) XML or JSON
     *
     * @return string Header value should look like <prefix><content><action>+<type>
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException If the content of request/response body doesn't exist
     */
    public function constructSpecialHeader( $object, $action = null, $type = null )
    {
        $value = self::CONTENT_HEADER_PREFIX;
        $value .= $object;
        $value .= $action;
        $value .= '+';
        $value .= ( !empty( $type ) ) ?
            strtolower( $type ) :
            $this->bodyType;

        return $value;
    }
}
