<?php
/**
 * File containing the ObjectController trait for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\Helpers;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Behat\Behat\Tester\Exception\PendingException;
use PHPUnit_Framework_Assert as Assertion;

/**
 * ObjectController should manage all actions that will interact with API objects
 */
trait ObjectController
{
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
     * Convert an object to a request
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object Object to be converted
     * @param string $type Type for conversion
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws InvalidArgumentException If the type is not known
     */
    protected function convertObjectTo( ValueObject $object, $type )
    {
        $type = strtolower( $type );
        switch( $type )
        {
            case 'json':
            case 'xml':
                $visitor = $this->getKernel()->getContainer()->get( 'ezpublish_rest.output.visitor.' . $type );
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
    protected function convertResponseBodyToObject( $responseBody, $contentTypeHeader )
    {
        try
        {
            $this->responseObject = $this->getKernel()->getContainer()->get( 'ezpublish_rest.input.dispatcher' )->parse(
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
    protected function getResponseObject()
    {
        if ( empty( $this->responseObject ) )
        {
            $this->responseObject = $this->convertResponseBodyToObject(
                $this->restDriver->getBody(),
                $this->restDriver->getHeader( 'content-type' )
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
    protected function addObjectToRequestBody( ValueObject $object, $type )
    {
        $request = $this->convertObjectTo( $object, $type );

        $this->restDriver->setBody( $request->getContent() );
    }

    /**
     * @When I make a(n) :type object
     *
     * This will create an object of the type passed for step by step be filled
     */
    public function makeObject( $type )
    {
        $this->createAnObject( $type );
    }

    /**
     * @When I set field :field to :value
     */
    public function setFieldToValue( $field, $value )
    {
        // normally fields are defined in lower camelCase
        $field = lcfirst( $field );

        $this->setValueObjectProperty( $this->requestObject, $field, $value );
    }

    /**
     * @Then response object has field :field with :value
     */
    public function assertObjectFieldHasValue( $field, $value )
    {
        $responseObject = $this->getResponseObject();
        $actualValue = $this->getValueObjectProperty( $responseObject, $field );

        Assertion::assertEquals(
            $actualValue,
            $value,
            "Expected '$field' property to have '$value' value but found '$actualValue' value"
        );
    }

    /**
     * @Then response has/contains a(n) :object object
     *
     * @param string $object Object should be the object name with namespace
     */
    public function assertResponseObject( $object )
    {
        $responseObject = $this->getResponseObject();

        Assertion::assertTrue(
            $responseObject instanceof $object,
            "Expect body object to be an instance of '$object' but got a '". get_class( $responseObject ) . "'"
        );
    }
}
