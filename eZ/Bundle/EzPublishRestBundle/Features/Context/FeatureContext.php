<?php
/**
 * File containing the FeatureContext class.
 *
 * This class contains general REST feature context for Behat.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context;

use EzSystems\BehatBundle\Features\Context\FeatureContext as BaseContext;
use eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient\RestClient;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\REST\Common\Message;
use Behat\Behat\Exception\PendingException;

/**
 * Feature context.
 */
class FeatureContext extends BaseContext
{
    /**
     * Rest client for all requests and responses
     *
     * @var eZ\Bundle\EzPublishRestBundle\Features\Context\RestClientInterface
     */
    public $restclient;

    /**
     * Since there is a need to prepare an object in several steps it needs to be
     * hold until sent to the request body
     *
     * @var eZ\Publish\API\Repository\Values\ValueObject
     */
    public $requestObject;

    /**
     * Same idea as the $requestObject, since we need to verify it step by step
     * it need to be stored (as object) for testing
     *
     * @var eZ\Publish\API\Repository\Values\ValueObject|Exception
     */
    public $responseObject;

    /**
     * @param array $parameters
     */
    public function __construct( array $parameters )
    {
        // set parent parameters
        parent::__construct( $parameters );

        // create a new REST Client
        $this->restclient = new RestClient();
    }

    /**
     * Convert an object to a request
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object Object to be converted
     * @param string $type Type for conversion
     *
     * @return Symfony\Component\HttpFoundation\Response
     *
     * @throws InvalidArgumentException
     */
    public function convertObjectTo( ValueObject $object, $type )
    {
        $type = strtolower( $type );
        switch( $type ) {
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

    /**
     * Gets an object property/field
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object The object to be updated
     * @param string $property Name of property or field
     *
     * @return mixed
     *
     * @throws InvalidArgumentException If the property/field is not found
     */
    protected function getProperty( ValueObject $object, $property )
    {
        if ( !is_object( $object ) )
        {
            throw new InvalidArgumentException( $object, 'is not an object' );
        }

        if ( property_exists( $object, $property ) )
        {
            return $object->$property;
        }
        else if ( method_exists( $object, 'setField' ) )
        {
            return $object->getField( $property );
        }
        else
        {
            throw new InvalidArgumentException( $property, "wasn't foun in '" . get_class( $object ) ."' object" );
        }
    }

    /**
     * Sets an object property/field to the intended value
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object The object to be updated
     * @param string $property Name of property or field
     * @param mixed  $value The value to set the property/field to
     *
     * @throws InvalidArgumentException If the property/field is not found
     */
    protected function setProperty( ValueObject $object, $property, $value )
    {
        if ( !is_object( $object ) )
        {
            throw new InvalidArgumentException( $object, 'is not an object' );
        }

        if ( property_exists( $object, $property ) )
        {
            $object->$property = $value;
        }
        else if ( method_exists( $object, 'setField' ) )
        {
            $object->setField( $property, $value );
        }
        else
        {
            throw new InvalidArgumentException( $property, "wasn't foun in '" . get_class( $object ) ."' object" );
        }
    }

    /**
     * Sets an objects properties
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object Object to be updated
     * @param array $values Associative array with properties => values
     */
    protected function setProperties( ValueObject $object, array $values )
    {
        if ( empty( $values ) )
        {
            return;
        }

        foreach ( $values as $property => $value )
        {
            $this->setProperty( $object, $property, $value );
        }
    }
}
