<?php

/**
 * File containing the RestContext for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context;

use Behat\Mink\Mink;
use Behat\MinkExtension\Context\MinkAwareContext;
use eZ\Publish\Core\REST\Client\Values\ErrorMessage;
use EzSystems\BehatBundle\Context\Api\Context;
use EzSystems\BehatBundle\Helper\Gherkin as GherkinHelper;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert as Assertion;
use Exception;

/**
 * RestContext is the core of the REST testing
 *   All SubContext (traits), helpers are loaded here
 *   Settings and client initializations is done here
 *   Also it contains all REST generic actions.
 */
class RestContext extends Context implements MinkAwareContext
{
    use SubContext\EzRest;
    use SubContext\Authentication;
    use SubContext\ContentTypeGroup;
    use SubContext\Exception;
    use SubContext\Views;
    use SubContext\User;

    const AUTHTYPE_BASICHTTP = 'http_basic';
    const AUTHTYPE_SESSION = 'session';

    const DEFAULT_URL = 'http://localhost/';
    const DEFAULT_DRIVER = 'GuzzleDriver';
    const DEFAULT_BODY_TYPE = 'json';

    const DEFAULT_AUTH_TYPE = self::AUTHTYPE_SESSION;

    /**
     * Rest driver for all requests and responses.
     *
     * @var \eZ\Bundle\EzPublishRestBundle\Features\Context\RestClient\DriverInterface
     */
    protected $restDriver;

    /** @var string */
    private $url;

    /** @var string */
    private $driver;

    /** @var \Behat\Mink\Mink */
    private $mink;

    /** @var array */
    private $minkParameters;

    /**
     * Initialize class.
     *
     * @param string $url    Base URL for REST calls
     * @param string $driver REST Driver to be used
     * @param string $json
     */
    public function __construct(
        $driver = self::DEFAULT_DRIVER,
        $type = self::DEFAULT_BODY_TYPE,
        $authType = self::DEFAULT_AUTH_TYPE
    ) {
        $this->driver = $driver;
        $this->restBodyType = $type;
        $this->authType = $authType;

        $this->setRestDriver($this->driver);
    }

    private function setUrl($url)
    {
        $this->url = $url;
        if (isset($this->restDriver)) {
            $this->restDriver->setHost($this->url);
        }
    }

    /**
     * Sets Mink instance.
     *
     * @param Mink $mink Mink session manager
     */
    public function setMink(Mink $mink)
    {
        $this->mink = $mink;
    }

    /**
     * Sets parameters provided for Mink.
     * While at it, take the base_url, and use it to build the one for the REST driver.
     *
     * @param array $parameters
     */
    public function setMinkParameters(array $parameters)
    {
        $this->minkParameters = $parameters;
        $this->setUrl($parameters['base_url'] . '/api/ezp/v2/');
    }

    /**
     * @BeforeScenario
     */
    private function resetDriver()
    {
        $this->setRestDriver($this->driver, $this->url);
    }

    /**
     * Create and set the REST driver to be used.
     *
     * @param string $restDriver REST driver class name
     */
    private function setRestDriver($restDriver)
    {
        $namespace = '\\' . __NAMESPACE__ . '\\RestClient\\';
        $driver = $namespace . $restDriver;
        $parent = $namespace . 'DriverInterface';

        if (
            empty($restDriver)
            || !class_exists($driver)
            || !is_subclass_of($driver, $parent)
        ) {
            throw new InvalidArgumentException('rest driver', $driver);
        }

        // create a new REST Driver
        $this->restDriver = new $driver();
        if (isset($this->url)) {
            $this->restDriver->setHost($this->url);
        }
    }

    /**
     * @When I create a :type request to :resource (url)
     */
    public function createRequest($type, $resource)
    {
        $this->restDriver->setMethod($type);
        $this->restDriver->setResource(
            $this->changeMappedValuesOnUrl($resource)
        );
        $this->responseObject = null;
    }

    /**
     * @When I send a :type request to :resource (url)
     */
    public function createAndSendRequest($type, $resource)
    {
        $this->createRequest($type, $resource);
        $this->restDriver->send();
    }

    /**
     * @When I set :header header with :value (value)
     */
    public function setHeader($header, $value)
    {
        $this->restDriver->setHeader($header, $value);
    }

    /**
     * @When I set headers:
     */
    public function setHeaders(TableNode $table)
    {
        $headers = GherkinHelper::convertTableToArrayOfData($table);

        foreach ($headers as $header => $value) {
            $this->iAddHeaderWithValue($header, $value);
        }
    }

    /**
     * @When I send the request
     */
    public function sendRequest()
    {
        $requestObject = $this->getRequestObject();
        if (!empty($requestObject)) {
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
    public function assertStatusCode($code)
    {
        $exceptionMessage = '';
        if ($code != $this->restDriver->getStatusCode() && $code >= 200 && $code < 400) {
            $errorMessage = $this->getResponseObject();
            if ($errorMessage instanceof ErrorMessage) {
                $exceptionMessage = <<< EOF

Server Error ({$errorMessage->code}): {$errorMessage->message}

{$errorMessage->description}

In {$errorMessage->file}:{$errorMessage->line}

{$errorMessage->trace}
EOF;
            } elseif ($errorMessage instanceof Exception) {
                $exceptionMessage = <<< EOF

Client Exception ({$errorMessage->getCode()}): {$errorMessage->getMessage()}

In {$errorMessage->getFile()}:{$errorMessage->getLine()}
EOF;
                // If previous exception is available it is most likely carrying info on server exception.
                if ($previous = $errorMessage->getPrevious()) {
                    $exceptionName = get_class($previous);
                    $exceptionMessage .= <<< EOF

Previous Exception $exceptionName ({$previous->getCode()}): {$previous->getMessage()}

In {$previous->getFile()}:{$previous->getLine()}

{$previous->getTraceAsString()}
EOF;
                }
            }
        }

        Assertion::assertEquals(
            $code,
            $this->restDriver->getStatusCode(),
            "Expected status code '$code' found '{$this->restDriver->getStatusCode()}'$exceptionMessage"
        );
    }

    /**
     * @Then response status message is :message
     */
    public function assertStatusMessage($message)
    {
        Assertion::assertEquals(
            strtolower($message),
            strtolower($this->restDriver->getStatusMessage()),
            "Expected status message '$message' found '{$this->restDriver->getStatusMessage()}'"
        );
    }

    /**
     * @Then response header :header exist
     */
    public function existResponseHeader($header)
    {
        Assertion::assertNotNull(
            $this->restDriver->getHeader($header),
            "Expected '$header' header not found"
        );
    }

    /**
     * @Then response header :header don't exist
     */
    public function dontExistResponseHeader($header)
    {
        Assertion::assertNull(
            $this->restDriver->getHeader($header),
            "Unexpected '$header' header found with '{$this->restDriver->getHeader($header)}' value"
        );
    }

    /**
     * @Then response header :header have :value (value)
     */
    public function assertHeaderHaveValue($header, $value)
    {
        Assertion::assertEquals(
            $value,
            $this->restDriver->getResponseHeader($header),
            "Expected '$header' header with '$value' found it with '{$this->restDriver->getHeader($header)}' value"
        );
    }

    /**
     * @Then response header :header don't have :value (value)
     */
    public function assertHeaderDontHaveValue($header, $value)
    {
        Assertion::assertNotEquals(
            $value,
            $this->restDriver->getResponseHeader($header),
            "Unexpected '$header' header found with '{$this->restDriver->getHeader($header)}' value"
        );
    }

    /**
     * @Then response body has :value (value)
     */
    public function responseBodyHasValue($value)
    {
        Assertion::assertEquals(
            $value,
            $this->restDriver->getBody(),
            "Expected body isn't equal to the actual one."
            . "\nExpected: "
            . print_r($value, true)
            . "\nActual: "
            . print_r($this->restDriver->getBody(), true)
        );
    }
}
