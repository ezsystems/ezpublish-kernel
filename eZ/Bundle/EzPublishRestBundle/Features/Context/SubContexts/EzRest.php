<?php
/**
 * File containing the EzRest for RestContext.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContexts;

use PHPUnit_Framework_Assert as Assertion;

/**
 * EzRest is the responsible to have all the specific REST actions of eZ Publish
 */
trait EzRest
{
    /**
     * @var string Type of request/response body [JSON/XML]
     */
    private $restBodyType = 'json';

    /**
     * Set body type of requests and responses
     *
     * @param string $type Type of the REST body
     */
    public function setRestBodyType( $type )
    {
        $type = strtolower( $type );
        switch ( $type )
        {
            case 'json':
            case 'xml':
                $this->restBodyType = $type;
                break;

            default:
                throw new \Exception( "REST body type '$type' not suported" );
        }
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

    /**
     * Make content-type/accept header with prefix and type
     */
    protected function makeObjectHeader( $object )
    {
        return 'application/vnd.ez.api.' . $object . '+' . $this->restBodyType;
    }

    /**
     * Decompose the header to get only the object type of the accept/conten-type headers
     * 
     * @return false|string Decomposed string if found, false other wise
     */
    protected function decomposeObjectHeader( $header )
    {
        preg_match( '/application\/vnd\.ez\.api\.(.*)\+(?:json|xml)/i', $header, $match );

        return empty( $match ) ? false : $match[1];
    }

    /**
     * @When I set header :header with/for :object object
     */
    public function setHeaderWithObject( $header, $object )
    {
        $value = $this->makeObjectHeader( $object );
        $this->restDriver->setHeader( $header, $value );
    }

    /**
     * @Then response header :header has/contains :object (object)
     */
    public function assertHeaderHasObject( $header, $object )
    {
        Assertion::assertEquals(
            $this->decomposeObjectHeader( $this->restDriver->getHeader( $header ) ),
            $object
        );
    }

    /**
     * @Then response error status code is :code
     */
    public function assertResponseErrorStatusCode( $code )
    {
        $errorStatusCode = $this->getResponseError( 'code' );

        Assertion::assertEquals(
            $code,
            $errorStatusCode,
            "Expected '$code' status code found '$errorStatusCode'"
        );
    }

    /**
     * @Then response error description is :errorDescriptionRegEx
     */
    public function assertResponseErrorDescription( $errorDescriptionRegEx )
    {
        $errorDescription = $this->getResponseError( 'description' );

        Assertion::assertEquals(
            preg_match( $errorDescriptionRegEx, $errorDescription ),
            1,
            "Expected to find a description that matched '$errorDescriptionRegEx' RegEx but found '$errorDescription'"
        );
    }

}
