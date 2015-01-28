<?php
/**
 * File containing the RestContext for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishRestBundle\Features\Context;

use EzSystems\BehatBundle\Context\Api\Context;
use EzSystems\BehatBundle\Helper\Gherkin as GherkinHelper;
use eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert as Assertion;

/**
 * RestContext is the core of the REST testing
 *   All SubContext (traits), helpers are loaded here
 *   Settings and client initializations is done here
 *   Also it contains all REST generic actions
 */
class RestContext extends Context
{
    use SubContext\EzRest;
    use SubContext\Authentication;
    use SubContext\ContentTypeGroup;
    use SubContext\Exception;

    const AUTHTYPE_BASICHTTP  = 'http_basic';
    const AUTHTYPE_SESSION    = 'session';

    const DEFAULT_URL = 'http://localhost/';
    const DEFAULT_DRIVER = 'GuzzleDriver';
    const DEFAULT_BODY_TYPE = 'json';

    const DEFAULT_AUTH_TYPE = self::AUTHTYPE_SESSION;

    /**
     * Rest driver for all requests and responses
     *
     * @var \eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient\RestClient
     */
    protected $restDriver;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $driver;

    /**
     * Initialize class
     *
     * @param string $url    Base URL for REST calls
     * @param string $driver REST Driver to be used
     * @param string $json
     *
     * @return void
     */
    public function __construct(
        $url = self::DEFAULT_URL,
        $driver = self::DEFAULT_DRIVER,
        $type = self::DEFAULT_BODY_TYPE,
        $authType = self::DEFAULT_AUTH_TYPE
    )
    {
        $this->driver = $driver;
        $this->url = $url;
        $this->restBodyType = $type;
        $this->authType = $authType;

        $this->setRestDriver( $this->driver, $this->url );
    }

    /**
     * @BeforeScenario
     */
    private function resetDriver()
    {
        $this->setRestDriver( $this->driver, $this->url );
    }

    /**
     * Create and set the REST driver to be used
     *
     * @param string $restDriver REST driver class name
     * @param string|null $restUrl Base URL for the REST calls
     */
    private function setRestDriver( $restDriver, $restUrl )
    {
        $namespace = '\\' . __NAMESPACE__ .  '\\RestClient\\';
        $driver = $namespace . $restDriver;
        $parent = $namespace . "DriverInterface";

        if (
            empty( $restDriver )
            || !class_exists( $driver )
            || !is_subclass_of( $driver, $parent )
        )
        {
            throw new InvalidArgumentException( 'rest driver', $driver );
        }

        // create a new REST Driver
        $this->restDriver = new $driver();
        $this->restDriver->setHost( $restUrl );
    }

    /**
     * @When I create a :type request to :resource (url)
     */
    public function createRequest( $type, $resource )
    {
        $this->restDriver->setMethod( $type );
        $this->restDriver->setResource(
            $this->changeMappedValuesOnUrl( $resource )
        );
        $this->responseObject = null;
    }

    /**
     * @When I send a :type request to :resource (url)
     */
    public function createAndSendRequest( $type, $resource )
    {
        $this->createRequest( $type, $resource );
        $this->restDriver->send();
    }

    /**
     * @When I set :header header with :value (value)
     */
    public function setHeader( $header, $value )
    {
        $this->restDriver->setHeader( $header, $value );
    }

    /**
     * @When I set headers:
     */
    public function setHeaders( TableNode $table )
    {
        $headers = GherkinHelper::convertTableToArrayOfData( $table );

        foreach ( $headers as $header => $value )
        {
            $this->iAddHeaderWithValue( $header, $value );
        }
    }

    /**
     * @When I send the request
     */
    public function sendRequest()
    {
        $requestObject = $this->getRequestObject();
        if ( ! empty( $requestObject ) )
        {
            $this->addObjectToRequestBody(
                $requestObject,
                $this->restBodyType
            );
        }
        $this->restDriver->send();
    }

    /**
     * @Then response status code is :code
     */
    public function assertStatusCode( $code )
    {
        Assertion::assertEquals(
            $code,
            $this->restDriver->getStatusCode(),
            "Expected status code '$code' found '{$this->restDriver->getStatusCode()}'"
        );
    }

    /**
     * @Then response status message is :message
     */
    public function assertStatusMessage( $message )
    {
        Assertion::assertEquals(
            strtolower( $message ),
            strtolower( $this->restDriver->getStatusMessage() ),
            "Expected status message '$message' found '{$this->restDriver->getStatusMessage()}'"
        );
    }

    /**
     * @Then response header :header exist
     */
    public function existResponseHeader( $header )
    {
        Assertion::assertNotNull(
            $this->restDriver->getHeader( $header ),
            "Expected '$header' header not found"
        );
    }

    /**
     * @Then response header :header don't exist
     */
    public function dontExistResponseHeader( $header )
    {
        Assertion::assertNull(
            $this->restDriver->getHeader( $header ),
            "Unexpected '$header' header found with '{$this->restDriver->getHeader( $header )}' value"
        );
    }

    /**
     * @Then response header :header have :value (value)
     */
    public function assertHeaderHaveValue( $header, $value )
    {
        Assertion::assertEquals(
            $value,
            $this->restDriver->getResponseHeader( $header ),
            "Expected '$header' header with '$value' found it with '{$this->restDriver->getHeader( $header )}' value"
        );
    }

    /**
     * @Then response header :header don't have :value (value)
     */
    public function assertHeaderDontHaveValue( $header, $value )
    {
        Assertion::assertNotEquals(
            $value,
            $this->restDriver->getResponseHeader( $header ),
            "Unexpected '$header' header found with '{$this->restDriver->getHeader( $header )}' value"
        );
    }

    /**
     * @Then response body has :value (value)
     */
    public function responseBodyHasValue( $value )
    {
        Assertion::assertEquals(
            $value,
            $this->restDriver->getBody(),
            "Expected body isn't equal to the actual one."
            . "\nExpected: "
            . print_r( $value, true )
            . "\nActual: "
            . print_r( $this->restDriver->getBody(), true )
        );
    }
}
