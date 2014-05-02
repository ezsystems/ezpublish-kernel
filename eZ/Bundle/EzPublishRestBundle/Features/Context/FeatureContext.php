<?php
/**
 * File containing the FeatureContext class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context;

use EzSystems\BehatBundle\Features\Context\FeatureContext as BaseContext;
use eZ\Bundle\EzPublishRestBundle\Features\Context\RestInternalSentences;
use eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient;
use eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext\ContentTypeGroupContext;
use eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext\AuthenticationContext;
use eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext\ErrorContext;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit_Framework_Assert as Assertion;

/**
 * Feature context.
 *
 * This class contains general REST feature context for Behat.
 */
class FeatureContext extends BaseContext implements RestInternalSentences
{
    /**
     * Rest client for all requests and responses
     *
     * @var \eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient\RestClient
     */
    public $restclient;

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
     * @var \eZ\Publish\API\Repository\Values\ValueObject|Exception
     */
    public $responseObject;

    /**
     * Last action (and last action status) are needed to verify the status
     * code an message
     *
     * @var string
     */
    protected $lastAction;

    /**
     * @var array Each array entry should have the "action" => [ code, "message" ]
     * @see FeatureContext::lastAction
     */
    protected $lastActionStatus = array(
        // action => array( <status code>, <status message> )
        "create" => array( 201, "created" ),
        "update" => array( 200, "ok" ),
    );

    /**
     * @param array $parameters
     */
    public function __construct( array $parameters )
    {
        // set parent parameters
        parent::__construct( $parameters );

        // create a new REST Client
        //$this->restclient = new RestClient\BuzzClient();
        $this->restclient = new RestClient\GuzzleClient();

        // sub contexts
        $this->useContext( 'Authentication', new AuthenticationContext( $this->restclient ) );
        $this->useContext( 'ContentTypeGroup', new ContentTypeGroupContext( $this->restclient ) );
        $this->useContext( 'Error', new ErrorContext( $this->restclient ) );
    }

    /**
     * Sets the last action
     *
     * @param string $action
     */
    public function setLastAction( $action )
    {
        $this->lastAction = $action;
    }

    /**
     * Get the code and message for the last action
     *
     * @return array Status data array( code, message )
     */
    public function getLastActionStatusCodeAndMessage()
    {
        if ( empty( $this->lastAction ) || empty( $this->lastActionStatus[$this->lastAction] ) )
        {
            $data = array( 200, "ok" );
        }
        else
        {
            $data = $this->lastActionStatus[$this->lastAction];
        }

        return $data;
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
                $visitor = $this->kernel->getContainer()->get( 'ezpublish_rest.output.visitor.' . $type );
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
            $this->responseObject = $this->kernel->getContainer()->get( 'ezpublish_rest.input.dispatcher' )->parse(
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
                $this->restclient->responseBody,
                $this->restclient->getResponseHeader( 'content-type' )
            );
        }

        return $this->responseObject;
    }

    /**
     * Create an object of the specified type
     *
     * @param string $objectType the name of the object to be created
     *
     * @throws PendingException When the object requested is not implemented yet
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
            $type = $this->restclient->bodyType;
        }

        // if there is no passed object go get it trough the request object
        if ( empty( $object ) )
        {
            $object = $this->requestObject;
        }

        $request = $this->convertObjectTo( $object, $type );

        $this->restclient->setBody( $request->getContent() );
    }

    public function iCreateRequest( $resourceUrl, $requestType )
    {
        $this->restclient->setResourceUrl( $resourceUrl );
        $this->restclient->setRequestType( $requestType );
    }

    public function iAddHeaderToObjectAction( $header, $action, $object )
    {
        $this->restclient->addSpecialHeader( $header, $object, $action );
    }

    public function iAddHeaderForObject( $header, $object )
    {
        $this->iAddHeaderToObjectAction( $header, null, $object );
    }

    public function iMakeAnObject( $objectType )
    {
        $this->createAnObject( $objectType );
    }

    public function iAddValueToField( $value, $field )
    {
        // normally fields are defined in lower camelCase
        $field = lcfirst( $field );

        $this->valueObjectHelper->setProperty( $this->requestObject, $field, $value );
    }

    public function iSendRequest()
    {
        if (
            empty( $this->restclient->body )
            && !empty( $this->requestObject )
            && !empty( $this->restclient->headers['content-type'] )
        )
        {
            $this->addObjectToRequestBody();
        }
        $this->restclient->sendRequest();
    }

    public function iAddHeaderWithValue( $header, $value )
    {
        $this->restclient->setHeader( $header, $value );
    }

    public function iAddHeaders( TableNode $table )
    {
        $headers = $this->convertTableToArrayOfData( $table );

        foreach ( $headers as $header => $value )
        {
            $this->iAddHeaderWithValue( $header, $value );
        }
    }

    public function iSeeResponseHeader( $header )
    {
        Assertion::assertNotNull(
            $this->restclient->getResponseHeader( $header ),
            "Expected '$header' header not found"
        );
    }

    public function iDonTSeeResponseHeader( $header )
    {
        Assertion::assertNull(
            $this->restclient->getResponseHeader( $header ),
            "Unexpected '$header' header found with '{$this->restclient->getResponseHeader( $header )}' value"
        );
    }

    public function iSeeResponseHeaderWithValue( $header, $value )
    {
        Assertion::assertEquals(
            $value,
            $this->restclient->getResponseHeader( $header ),
            "Expected '$header' header with '$value' found it with '{$this->restclient->getResponseHeader( $header )}' value"
        );
    }

    public function iDonTSeeResponseHeaderWithValue( $header, $value )
    {
        Assertion::assertNotEquals(
            $value,
            $this->restclient->getResponseHeader( $header ),
            "Unexpected '$header' header found with '{$this->restclient->getResponseHeader( $header )}' value"
        );
    }

    public function iOnlySeeResponseHeaders( TableNode $table )
    {
        $expectHeaders = $this->convertTableToArrayOfData( $table );
        $actualHeaders = $this->restclient->getResponseHeaders();

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

    public function iSeeResponseHeaders( TableNode $table )
    {
        $expectHeaders = $this->convertTableToArrayOfData( $table );
        $actualHeaders = $this->restclient->getResponseHeaders();

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
     * @todo Implementation
     */
    public function iSeeResponseBodyWith( PyStringNode $body )
    {
        throw new PendingException( "Need to be implemented: iSeeBodyWith" );
    }

    public function iSeeResponseBodyWithObject( $object )
    {
        $responseObject = $this->getResponseObject();

        Assertion::assertTrue(
            $responseObject instanceof $object,
            "Expect body object to be an instance of '$object' but got a '". get_class( $responseObject ) . "'"
        );
    }

    public function iSeeResponseObjectWithFieldValue( $field, $value )
    {
        $responseObject = $this->getResponseObject();
        $actualValue = $this->valueObjectHelper->getProperty( $responseObject, $field );

        Assertion::assertEquals(
            $actualValue,
            $value,
            "Expected '$field' property to have '$value' value but found '$actualValue' value"
        );
    }

    public function iSeeResponseBodyWithValue( $value )
    {
        Assertion::assertEquals(
            $value,
            $this->restclient->getResponseBody(),
            "Expected body isn't equal to the actual one."
            . "\nExpected: "
            . print_r( $value, true )
            . "\nActual: "
            . print_r( $this->restclient->getResponseBody(), true )
        );
    }

    public function iSeeResponseHeaderForObject( $header, $object )
    {
        $this->iSeeResponseHeaderToObjectAction( $header, null, $object );
    }

    public function iSeeResponseHeaderToObjectAction( $header, $action, $object )
    {
        $expected = $this->restclient->constructSpecialHeader( $object, $action );
        $expected = substr( $expected, 0, strpos( $expected, '+' ) );
        $actual = $this->restclient->getResponseHeader( $header );
        $actual = substr( $actual, 0, strpos( $actual, '+' ) );
        Assertion::assertEquals(
            $expected,
            $actual,
            "Expected '$header' header with '$expected' value found it with '$actual' value "
        );
    }

    public function iSeeResponseStatusCode( $statusCode )
    {
        Assertion::assertEquals(
            $statusCode,
            $this->restclient->getResponseStatusCode(),
            "Expected status code '$statusCode' found '{$this->restclient->getResponseStatusCode()}'"
        );
    }

    public function iSeeResponseStatusMessage( $statusMessage )
    {
        Assertion::assertEquals(
            strtolower( $statusMessage ),
            strtolower( $this->restclient->getResponseStatusMessage() ),
            "Expected status message '$statusMessage' found '{$this->restclient->getResponseStatusMessage()}'"
        );
    }

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

    public function iSeeResponseErrorWithDescription( $errorDescriptionRegEx )
    {
        $errorDescription = $this->getResponseError( 'description' );

        Assertion::assertEquals(
            preg_match( $errorDescriptionRegEx, $errorDescription ),
            1,
            "Expected to find a description that matched '$errorDescriptionRegEx' RegEx but found '$errorDescription'"
        );
    }

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
