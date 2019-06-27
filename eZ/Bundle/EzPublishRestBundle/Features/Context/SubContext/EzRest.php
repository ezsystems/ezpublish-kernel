<?php

/**
 * File containing the EzRest for RestContext.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use eZ\Publish\Core\REST\Client\Values\ViewInput;
use EzSystems\BehatBundle\Helper\ValueObject as ValueObjectHelper;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\REST\Server\Values\SessionInput;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\Assert as Assertion;

/**
 * EzRest is the responsible to have all the specific REST actions of eZ Publish.
 */
trait EzRest
{
    /** @var string Type of request/response body [JSON/XML] */
    private $restBodyType = 'json';

    /**
     * Since there is a need to prepare an object in several steps it needs to be
     * hold until sent to the request body.
     *
     * @var \eZ\Publish\API\Repository\Values\ValueObject
     */
    public $requestObject;

    /**
     * Same idea as the $requestObject, since we need to verify it step by step
     * it need to be stored (as object) for testing.
     *
     * @var \eZ\Publish\API\Repository\Values\ValueObject|\Exception
     */
    public $responseObject;

    /**
     * @When I set header :header with/for :object object
     */
    public function setHeaderWithObject($header, $object)
    {
        $value = $this->makeObjectHeader($object);
        $this->restDriver->setHeader($header, $value);
    }

    /**
     * @Then response header :header has/contains :object (object)
     */
    public function assertHeaderHasObject($header, $object)
    {
        Assertion::assertEquals(
            $this->decomposeObjectHeader($this->restDriver->getHeader($header)),
            $object,
            $this->restDriver->getBody()
        );
    }

    /**
     * @Then response error status code is :code
     */
    public function assertResponseErrorStatusCode($code)
    {
        $errorStatusCode = $this->getResponseError('code');

        Assertion::assertEquals(
            $code,
            $errorStatusCode,
            "Expected '$code' status code found '$errorStatusCode' with message:" . $this->restDriver->getStatusMessage()
        );
    }

    /**
     * @Then response error description is :errorDescriptionRegEx
     */
    public function assertResponseErrorDescription($errorDescriptionRegEx)
    {
        $errorDescription = $this->getResponseError('description');

        Assertion::assertEquals(
            preg_match($errorDescriptionRegEx, $errorDescription),
            1,
            "Expected to find a description that matched '$errorDescriptionRegEx' RegEx but found '$errorDescription'"
        );
    }

    /**
     * @When I make a(n) :type object
     *
     * This will create an object of the type passed for step by step be filled
     */
    public function makeObject($type)
    {
        $this->createRequestObject($type);
    }

    /**
     * @When I set field :field to :value
     */
    public function setFieldToValue($field, $value)
    {
        // normally fields are defined in lower camelCase
        $field = lcfirst($field);

        ValueObjectHelper::setProperty($this->getRequestObject(), $field, $value);
    }

    /**
     * @When I set field :field to an empty array
     */
    public function setFieldToEmptyArray($field)
    {
        $this->setFieldToValue($field, []);
    }

    /**
     * @Then response object has field :field with :value
     */
    public function assertObjectFieldHasValue($field, $value)
    {
        $responseObject = $this->getResponseObject();
        $actualValue = ValueObjectHelper::getProperty($responseObject, $field);

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
    public function assertResponseObject($object)
    {
        $responseObject = $this->getResponseObject();

        Assertion::assertTrue(
            $responseObject instanceof $object,
            "Expect body object to be an instance of '$object' but got a '" . get_class($responseObject) . "'"
        );
    }

    /**
     * @When I set the Content-Type header to :contentType in version :version
     */
    public function iSetTheContentTypeHeaderToInVersion($contentType, $version)
    {
        $this->restDriver->setHeader('Content-Type', "$contentType+$this->restBodyType; version=$version");
    }

    /**
     * Set body type of requests and responses.
     *
     * @param string $type Type of the REST body
     */
    protected function setRestBodyType($type)
    {
        $type = strtolower($type);
        switch ($type) {
            case 'json':
            case 'xml':
                $this->restBodyType = $type;
                break;

            default:
                throw new \Exception("REST body type '$type' not suported");
        }
    }

    /**
     * Get property from the returned Exception.
     *
     * @param string $property Property to return
     *
     * @return int|mixed|string Property
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If property is not defined
     */
    protected function getResponseError($property)
    {
        $exception = $this->getResponseObject();

        if (!$exception instanceof \Exception) {
            throw new InvalidArgumentException('response object', 'is not an exception');
        }

        switch ($property) {
            case 'code':
                return $exception->getCode();

            case 'description':
            case 'message':
                return $exception->getMessage();
        }

        throw new InvalidArgumentException($property, 'is invalid');
    }

    /**
     * Make content-type/accept header with prefix and type.
     */
    protected function makeObjectHeader($object)
    {
        return 'application/vnd.ez.api.' . $object . '+' . $this->restBodyType;
    }

    /**
     * Decompose the header to get only the object type of the accept/conten-type headers.
     *
     * @return false|string Decomposed string if found, false other wise
     */
    protected function decomposeObjectHeader($header)
    {
        preg_match('/application\/vnd\.ez\.api\.(.*)\+(?:json|xml)/i', $header, $match);

        return empty($match) ? false : $match[1];
    }

    /**
     * Get the response object (if it's not converted do the conversion also).
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    public function getResponseObject()
    {
        if (empty($this->responseObject)) {
            $this->responseObject = $this->convertResponseBodyToObject(
                $this->restDriver->getBody(),
                $this->restDriver->getHeader('content-type')
            );
        }

        return $this->responseObject;
    }

    /**
     * Get the request object.
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    public function getRequestObject()
    {
        return $this->requestObject;
    }

    /**
     * Convert an object and add it to the body/content of the request.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object Object to be converted
     * @param string $type Type for the body of the request (XML, JSON)
     */
    protected function addObjectToRequestBody(ValueObject $object, $type)
    {
        $request = $this->convertObjectTo($object, $type);

        $this->restDriver->setBody($request->getContent());
    }

    /**
     * Convert an object to a request.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object Object to be converted
     * @param string $type Type for conversion
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws InvalidArgumentException If the type is not known
     */
    protected function convertObjectTo(ValueObject $object, $type)
    {
        $type = strtolower($type);
        switch ($type) {
            case 'json':
            case 'xml':
                $visitor = $this->getKernel()->getContainer()->get('ezpublish_rest.output.visitor.' . $type);
                break;

            default:
                throw new InvalidArgumentException('rest body type', $type);
        }

        return $visitor->visit($object);
    }

    /**
     * Convert the body/content of a response into an object.
     *
     * @param string $responseBody Body/content of the response (with the object)
     * @param string $contentTypeHeader Value of the content-type header
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    public function convertResponseBodyToObject($responseBody, $contentTypeHeader)
    {
        try {
            $this->responseObject = $this->getKernel()->getContainer()->get('ezpublish_rest.input.dispatcher')->parse(
                new Message(
                    ['Content-Type' => $contentTypeHeader],
                    $responseBody
                )
            );
        } catch (\Exception $e) {
            // when errors/exceptions popup on form the response we need also to
            // test/verify them
            $this->responseObject = $e;
        }

        return $this->responseObject;
    }

    /**
     * Create an object of the specified type.
     *
     * @param string $objectType the name of the object to be created
     *
     * @throws \Behat\Behat\Exception\PendingException When the object requested is not implemented yet
     */
    protected function createRequestObject($objectType)
    {
        $repository = $this->getRepository();

        switch ($objectType) {
            case 'SessionInput':
                $this->requestObject = new SessionInput();
                break;
            case 'ContentTypeGroupCreateStruct':
                $this->requestObject = $repository
                    ->getContentTypeService()
                    ->newContentTypeGroupCreateStruct('identifier');
                break;

            case 'ContentTypeGroupUpdateStruct':
                $this->requestObject = $repository
                    ->getContentTypeService()
                    ->newContentTypeGroupUpdateStruct();
                break;

            case 'ViewInput':
                $this->requestObject = new ViewInput();
                break;

            default:
                throw new InvalidArgumentException($objectType, 'type is not defined yet');
        }
    }
}
