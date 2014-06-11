<?php
/**
 * File containing the RestContext class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context;

use EzSystems\BehatBundle\Context\ApiContext;
use eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;
use eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit_Framework_Assert as Assertion;

class RestContext extends ApiContext implements RestSentences
{
    /**
     * Rest client for all requests and responses
     *
     * @var \eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient\RestClient
     */
    public $restClient;

    /**
     * Since there is a need to prepare an object in several steps it needs to be
     * hold until sent to the request body
     *
     * @var \eZ\Publish\API\Repository\Values\ValueObject
     */
    public $requestObject;

    /**
     * Same idea as the $requestObject, since we need to verify it step by step
     * it need to be stored (as object) for testing
     *
     * @var \eZ\Publish\API\Repository\Values\ValueObject|\Exception
     */
    public $responseObject;

    /**
     * @param array $parameters
     */
    public function __construct( array $parameters )
    {
        // set parent parameters
        parent::__construct( $parameters );

        $rest_url = !empty( $parameters['rest_url'] ) ?
            $parameters['rest_url'] :
            null;

        // create a new REST Client
        $this->restClient = new RestClient\BuzzClient( $rest_url );

        // sub contexts
        $this->useContext( 'Authentication', new SubContext\Authentication( $this->restClient ) );
        $this->useContext( 'ContentTypeGroup', new SubContext\ContentTypeGroup( $this->restClient ) );
        $this->useContext( 'Exception', new SubContext\Exception( $this->restClient ) );
    }

    /**
     * Convert an object to a request
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object Object to be converted
     * @param string $type Type for conversion
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws InvalidArgumentException
     */
    public function convertObjectTo( ValueObject $object, $type )
    {
        $type = strtolower( $type );
        switch( $type )
        {
            case 'json':
            case 'xml':
                $visitor = $this->getSubContext( 'Common' )->kernel->getContainer()->get( 'ezpublish_rest.output.visitor.' . $type );
                break;

            default:
                throw new InvalidArgumentException( 'rest body type', $type );
        }

        return $visitor->visit( $object );
    }

    /**
     * Convert the body/content of a response into an object
     *
     * @param string $responseBody Body/content of the response (with the object)
     * @param string $contentTypeHeader Value of the content-type header
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    public function convertResponseBodyToObject( $responseBody, $contentTypeHeader )
    {
        try
        {
            $this->responseObject = $this->getSubContext( 'Common' )->kernel->getContainer()->get( 'ezpublish_rest.input.dispatcher' )->parse(
                new Message(
                    array( 'Content-Type' => $contentTypeHeader ),
                    $responseBody
                )
            );
        }
        // when errors/exceptions popup on form the response we need also to
        // test/verify them
        catch ( \Exception $e )
        {
            $this->responseObject = $e;
        }

        return $this->responseObject;
    }

    /**
     * Get the response object (if it's not converted do the conversion also)
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    public function getResponseObject()
    {
        if ( empty( $this->responseObject ) )
        {
            $this->responseObject = $this->convertResponseBodyToObject(
                $this->restClient->responseBody,
                $this->restClient->getResponseHeader( 'content-type' )
            );
        }

        return $this->responseObject;
    }

    /**
     * Create an object of the specified type
     *
     * @param string $objectType the name of the object to be created
     *
     * @throws \Behat\Behat\Exception\PendingException When the object requested is not implemented yet
     */
    protected function createAnObject( $objectType )
    {
        $repository = $this->getRepository();

        switch( $objectType ) {
            case "ContentTypeGroupCreateStruct":
                $this->requestObject = $repository
                    ->getContentTypeService()
                    ->newContentTypeGroupCreateStruct( 'identifier' );
                break;
            case "ContentTypeGroupUpdateStruct":
                $this->requestObject = $repository
                    ->getContentTypeService()
                    ->newContentTypeGroupUpdateStruct();
                break;

            default:
                throw new PendingException( "Make object of '$objectType' type is not defined yet" );
        }
    }

    /**
     * Convert an object and add it to the body/content of the request
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object Object to be converted
     * @param string $type Type for the body of the request (XML, JSON)
     */
    public function addObjectToRequestBody( ValueObject $object = null, $type = null )
    {
        // if no type is defined go get it from the request
        if ( empty( $type ) )
        {
            $type = $this->restClient->bodyType;
        }

        // if there is no passed object go get it trough the request object
        if ( empty( $object ) )
        {
            $object = $this->requestObject;
        }

        $request = $this->convertObjectTo( $object, $type );

        $this->restClient->setBody( $request->getContent() );
    }

    /**
     * Get property from the returned Exception
     *
     * @param string $property Property to return
     *
     * @return int|mixed|string Property
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If property is not defined
     */
    protected function getResponseError( $property )
    {
        $exception = $this->getResponseObject();

        if ( !$exception instanceof \Exception )
        {
            throw new InvalidArgumentException( 'response object', 'is not an exception' );
        }

        switch ( $property )
        {
            case 'code':
                return $exception->getCode();

            case 'description':
            case 'message':
                return $exception->getMessage();
        }

        throw new InvalidArgumentException( 'property', $property . ' is invalid' );
    }

    protected function changeMappedValuesOnUrl( $url )
    {
        $newUrl = "";
        foreach ( explode( '/', $url ) as $chunk )
        {
            $newChunk = $this->getSubContext( 'Common' )->getValuesFromMap( $chunk );
            if ( empty( $newChunk ) )
            {
                $newChunk = $chunk;
            }

            $newUrl .= '/' . $newChunk;
        }

        return preg_replace( '/\/\//', '/', $newUrl );
    }

    /**
     * When I create a "<requestType>" request to "<resourceUrl>"
     */
    public function iCreateRequest( $requestType, $resourceUrl )
    {
        $this->restClient->setResourceUrl(
            $this->changeMappedValuesOnUrl( $resourceUrl )
        );
        $this->restClient->setRequestType( $requestType );
    }

    /**
     * When I send a "<requestType>" request to "<resourceUrl>"
     */
    public function iCreateAndSendRequest( $requestType, $resourceUrl )
    {
        $this->iCreateRequest( $requestType, $resourceUrl );
        $this->iSendRequest();
    }

    /**
     * When I add "<header>" header (?:to|with) "<action>" (?:an|a|for|to|the|of) "<object>"
     *
     * Sentences examples:
     *  - I add content-type header to "Create" an "ContentType"
     *  - I add content-type header to "List" the "View
     *
     * Result example:
     *      Content-type: <header-prefix><object><action>+<content-type>
     *      Content-type: application/vnd.ez.api.ContentTypeGroupInput+xml
     *
     * Header can be:
     *  - accept
     *  - content-type
     */
    public function iAddHeaderToObjectAction( $header, $action, $object )
    {
        $this->restClient->addSpecialHeader( $header, $object, $action );
    }

    /**
     * When I add "<header>" header (?:for|with) (?:an|a|to|the|of) "<object>"
     *
     * Sentences examples:
     *  - I add accept header for "ContentType"
     *
     * Result example:
     *      Accept: <header-prefix><object>+<content-type>
     *      Accept: application/vnd.ez.api.ContentTypeGroup+xml
     *
     * Header can be:
     *  - accept
     *  - content-type
     */
    public function iAddHeaderForObject( $header, $object )
    {
        $this->iAddHeaderToObjectAction( $header, null, $object );
    }

    /**
     * When I make (?:an |a |)"<objectType>" object
     *
     * This will create an object of the type passed for step by step be filled
     */
    public function iMakeAnObject( $objectType )
    {
        $this->createAnObject( $objectType );
    }

    /**
     * When I add (?:the |)"<value>" value to "<field>" field
     */
    public function iAddValueToField( $value, $field )
    {
        // normally fields are defined in lower camelCase
        $field = lcfirst( $field );

        $this->getSubContext( 'Common' )->valueObjectHelper->setProperty( $this->requestObject, $field, $value );
    }

    /**
     * When I add "<header>" header with "<value>" value
     */
    public function iAddHeaderWithValue( $header, $value )
    {
        $this->restClient->setHeader( $header, $value );
    }

    /**
     * When I add headers:
     */
    public function iAddHeaders( TableNode $table )
    {
        $headers = $this->getSubContext( 'Common' )->convertTableToArrayOfData( $table );

        foreach ( $headers as $header => $value )
        {
            $this->iAddHeaderWithValue( $header, $value );
        }
    }

    /**
     * When I send (?:the |)request
     */
    public function iSendRequest()
    {
        if (
            empty( $this->restClient->body )
            && !empty( $this->requestObject )
            && !empty( $this->restClient->headers['content-type'] )
        )
        {
            $this->addObjectToRequestBody();
        }
        $this->restClient->sendRequest();
    }

    /**
     * Then I see "<header>" header
     */
    public function iSeeResponseHeader( $header )
    {
        Assertion::assertNotNull(
            $this->restClient->getResponseHeader( $header ),
            "Expected '$header' header not found"
        );
    }

    /**
     * Then I (?:don\'t|do not) see "<header>" header
     */
    public function iDonTSeeResponseHeader( $header )
    {
        Assertion::assertNull(
            $this->restClient->getResponseHeader( $header ),
            "Unexpected '$header' header found with '{$this->restClient->getResponseHeader( $header )}' value"
        );
    }

    /**
     * Then I see "<header>" header with "<value>" value
     */
    public function iSeeResponseHeaderWithValue( $header, $value )
    {
        Assertion::assertEquals(
            $value,
            $this->restClient->getResponseHeader( $header ),
            "Expected '$header' header with '$value' found it with '{$this->restClient->getResponseHeader( $header )}' value"
        );
    }

    /**
     * Then I (?:don\'t|do not) see "<header>" header with "<value>" value
     */
    public function iDonTSeeResponseHeaderWithValue( $header, $value )
    {
        Assertion::assertNotEquals(
            $value,
            $this->restClient->getResponseHeader( $header ),
            "Unexpected '$header' header found with '{$this->restClient->getResponseHeader( $header )}' value"
        );
    }

    /**
     * When I only see headers:
     */
    public function iOnlySeeResponseHeaders( TableNode $table )
    {
        $expectHeaders = $this->getSubContext( 'Common' )->convertTableToArrayOfData( $table );
        $actualHeaders = $this->restClient->getResponseHeaders();

        foreach ( $expectHeaders as $header => $value )
        {
            if ( is_int( $header ) )
            {
                $header = $value;
            }

            Assertion::assertTrue(
                array_key_exists( $header, $actualHeaders ),
                "Expected '$header' header not found"
            );

            if ( $header !== $value )
            {
                Assertion::assertEquals(
                    $value,
                    $actualHeaders[$header],
                    "Found '$header' header with '{$actualHeaders[$header]}' value but expected '$value' value"
                );
            }

            unset( $actualHeaders[$header] );
        }

        Assertion::assertEmpty(
            $actualHeaders,
            "Unexpected headers found: " . print_r( $actualHeaders, true )
        );
    }

    /**
     * When I see headers:
     */
    public function iSeeResponseHeaders( TableNode $table )
    {
        $expectHeaders = $this->getSubContext( 'Common' )->convertTableToArrayOfData( $table );
        $actualHeaders = $this->restClient->getResponseHeaders();

        foreach ( $expectHeaders as $header => $value )
        {
            if ( is_int( $header ) )
            {
                $header = $value;
            }

            Assertion::assertTrue(
                array_key_exists( $header, $actualHeaders ),
                "Expected '$header' header not found"
            );

            if ( $header !== $value )
            {
                Assertion::assertEquals(
                    $value,
                    $actualHeaders[$header],
                    "Found '$header' header with '{$actualHeaders[$header]}' value but expected '$value' value"
                );
            }

            unset( $actualHeaders[$header] );
        }
    }

    /**
     * Then I see body with:
     *       """
     *          data
     *       """
     *
     * @todo Implementation
     */
    public function iSeeResponseBodyWith( PyStringNode $body )
    {
        throw new PendingException( "Need to be implemented: iSeeBodyWith" );
    }

    /**
     * Then I see response body with "<object>" object
     *
     * @param string $object Object should be "ContentType" or "UserCreate", ....
     */
    public function iSeeResponseBodyWithObject( $object )
    {
        $responseObject = $this->getResponseObject();

        Assertion::assertTrue(
            $responseObject instanceof $object,
            "Expect body object to be an instance of '$object' but got a '". get_class( $responseObject ) . "'"
        );
    }

    /**
     * Then I see response object field "<field>" with "<value>" value
     */
    public function iSeeResponseObjectWithFieldValue( $field, $value )
    {
        $responseObject = $this->getResponseObject();
        $actualValue = $this->getSubContext( 'Common' )->valueObjectHelper->getProperty( $responseObject, $field );

        Assertion::assertEquals(
            $actualValue,
            $value,
            "Expected '$field' property to have '$value' value but found '$actualValue' value"
        );
    }

    /**
     * Then I see body with "<value>" value
     */
    public function iSeeResponseBodyWithValue( $value )
    {
        Assertion::assertEquals(
            $value,
            $this->restClient->getResponseBody(),
            "Expected body isn't equal to the actual one."
            . "\nExpected: "
            . print_r( $value, true )
            . "\nActual: "
            . print_r( $this->restClient->getResponseBody(), true )
        );
    }

    /**
     * Then I see "<header>" header (?:for|with) (?:an|a|to|the) "<object>"
     */
    public function iSeeResponseHeaderForObject( $header, $object )
    {
        $this->iSeeResponseHeaderToObjectAction( $header, null, $object );
    }

    /**
     * Then /^I see "<header>" header to "<action>" (?:an|a|for|to|the) "<object>"
     */
    public function iSeeResponseHeaderToObjectAction( $header, $action, $object )
    {
        $expected = $this->restClient->constructSpecialHeader( $object, $action );
        $expected = substr( $expected, 0, strpos( $expected, '+' ) );
        $actual = $this->restClient->getResponseHeader( $header );
        $actual = substr( $actual, 0, strpos( $actual, '+' ) );
        Assertion::assertEquals(
            $expected,
            $actual,
            "Expected '$header' header with '$expected' value found it with '$actual' value "
        );
    }

    /**
     * Then I see <statusCode> status code$/
     */
    public function iSeeResponseStatusCode( $statusCode )
    {
        Assertion::assertEquals(
            $statusCode,
            $this->restClient->getResponseStatusCode(),
            "Expected status code '$statusCode' found '{$this->restClient->getResponseStatusCode()}'"
        );
    }

    /**
     * Then I see "<statusMessage>" status (?:reason phrase|message)$/
     */
    public function iSeeResponseStatusMessage( $statusMessage )
    {
        Assertion::assertEquals(
            strtolower( $statusMessage ),
            strtolower( $this->restClient->getResponseStatusMessage() ),
            "Expected status message '$statusMessage' found '{$this->restClient->getResponseStatusMessage()}'"
        );
    }

    /**
     * Then I see response error description with "<errorDescriptionRegEx>"
     */
    public function iSeeResponseErrorWithDescription( $errorDescriptionRegEx )
    {
        $errorDescription = $this->getResponseError( 'description' );

        Assertion::assertEquals(
            preg_match( $errorDescriptionRegEx, $errorDescription ),
            1,
            "Expected to find a description that matched '$errorDescriptionRegEx' RegEx but found '$errorDescription'"
        );
    }

    /**
     * Then I see response error <statusCode> status code
     */
    public function iSeeResponseErrorStatusCode( $statusCode )
    {
        $errorStatusCode = $this->getResponseError( 'code' );

        Assertion::assertEquals(
            $statusCode,
            $errorStatusCode,
            "Expected '$statusCode' status code found '$errorStatusCode'"
        );
    }
}
