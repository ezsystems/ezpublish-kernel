<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use PHPUnit_Framework_Assert as Assertion;

class ViewConfigurationContext implements Context, SnippetAcceptingContext
{
    const BLOG_LOCATION_ID = 90;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \eZ\Publish\API\Repository\LocationService
     */
    private $locationService;

    /**
     * @var NavigationContext
     */
    private $navigationContext;

    /**
     * @var array
     */
    private $currentViewConfiguration;

    public function __construct(ConfigResolverInterface $configResolver, LocationService $locationService)
    {
        $this->configResolver = $configResolver;
        $this->locationService = $locationService;
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->navigationContext = $environment->getContext('eZ\Bundle\EzPublishCoreBundle\Features\Context\NavigationContext');
    }

    /**
     * Looks up and asserts a view configuration existence.
     *
     * @param string $name The name of the view configuration block (blog, landing_page, ...)
     * @param string $type The type of view configuration (content_view, block_view...)
     * @param string $viewType full, line, ...
     *
     * @Given /^there is a ([a-z0-9_-]*) ([a-z0-9_]+) configuration$/
     * @Given /^there is a ([a-z0-9_-]*) ([a-z0-9_]+) configuration for the ([a-z0-9_]+) viewType$/
     *
     * @return array The matching view configuration
     */
    public function thereIsAViewConfiguration($name, $type, $viewType = 'full')
    {
        Assertion::assertTrue($this->configResolver->hasParameter($type));
        $configuration = $this->configResolver->getParameter($type);

        Assertion::assertArrayHasKey(
            $viewType,
            $configuration,
            "Failed asserting that there are $type configurations for the '$viewType' viewType"
        );

        Assertion::assertArrayHasKey(
            $name,
            $configuration[$viewType],
            "Failed asserting that there is a '$viewType' query_type_view configuration named '$name'"
        );

        $this->currentViewConfiguration = $configuration[$viewType][$name];

        return $this->currentViewConfiguration;
    }

    /**
     * @Given /^there is a "([^"]*)" ([a-z0-9_]+) configuration for the "([^"]*)" viewType$/
     */
    public function thereIsAViewConfigurationForTheViewTypeWithTheTemplate($viewConfigName, $what, $viewType)
    {
        /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver */
        $queryTypeViewConfiguration = $this->configResolver->getParameter($what);

        Assertion::assertArrayHasKey(
            $viewType,
            $queryTypeViewConfiguration,
            "Failed asserting that there are $what configurations for the viewType $viewType"
        );

        Assertion::assertArrayHasKey(
            $viewConfigName,
            $queryTypeViewConfiguration[$viewType],
            "Failed asserting that there is a '$viewType' query_type_view configuration named '$viewConfigName'"
        );

        $this->currentViewConfiguration = $queryTypeViewConfiguration[$viewType][$viewConfigName];
    }

    /**
     * @Given /^that configuration has "([^"]*)" set to "([^"]*)"$/
     */
    public function configurationHasThePropertySetToTheValue($propertyName, $propertyValue)
    {
        Assertion::assertNotNull($this->currentViewConfiguration, 'No currentViewConfiguration was set');

        Assertion::assertArrayHasKey(
            $propertyName,
            $this->currentViewConfiguration,
            "Failed asserting that the query_type_view configuration sets the $propertyName property"
        );

        Assertion::assertEquals(
            $propertyValue,
            $this->currentViewConfiguration[$propertyName],
            "Failed asserting that the query_type_view configuration sets the $propertyName property to $propertyValue"
        );
    }

    /**
     * @Given /^that configuration has "([^"]*)" set to the boolean "([^"]*)"$/
     */
    public function configurationHasThePropertySetToTheBooleanValue($propertyName, $propertyValue)
    {
        if ($propertyValue === 'true') {
            $propertyValue = true;
        } elseif ($propertyValue === 'false') {
            $propertyValue = false;
        } else {
            throw new InvalidArgumentException('propertyValue', "Unknown boolean value '$propertyValue''");
        }

        $this->configurationHasThePropertySetToTheValue($propertyName, $propertyValue);
    }

    /**
     * @Given /^that configuration matches on "([^"]*)" "([^"]*)"$/
     */
    public function configurationHasMatch($matcherName, $matcherValue)
    {
        Assertion::assertNotNull($this->currentViewConfiguration, 'No currentViewConfiguration was set');

        $matchConfig = $this->currentViewConfiguration['match'];

        Assertion::assertArrayHasKey(
            $matcherName,
            $matchConfig,
            "Failed asserting that the view has a matcher '$matcherName'"
        );

        if (is_string($matcherValue)) {
            Assertion::assertEquals(
                $matcherValue,
                $matchConfig[$matcherName],
                "Failed asserting that the view matches on '$matcherName: $matcherValue'"
            );
        } elseif (is_array($matcherValue)) {
            Assertion::assertArraySubset(
                $matcherValue,
                $matchConfig[$matcherName],
                "Failed asserting that the view matches on '$matcherName' with the given array"
            );
        }
    }

    /**
     * @Given /^it sets the controller to \'([^\']*)\'$/
     */
    public function itSetsTheControllerTo($controller)
    {
        Assertion::assertNotNull($this->currentViewConfiguration, 'No current view configuration was set');
        Assertion::assertArrayHasKey('controller', $this->currentViewConfiguration);
        Assertion::assertEquals($controller, $this->currentViewConfiguration['controller']);
    }

    /**
     * @Given /^it sets the parameter "([^"]*)" to a valid QueryType name$/
     */
    public function itSetsParameterToAValidQueryTypeName($parameter)
    {
        $this->itSetsParameterTo($parameter, 'EzPlatformBehatBundle:LatestContent');
    }

    /**
     * @Given /^it sets the parameter "([^"]*)" to "([^"]*)"$/
     */
    public function itSetsParameterTo($parameterName, $parameterValue)
    {
        Assertion::assertNotNull($this->currentViewConfiguration, 'No current view configuration was set');
        Assertion::assertArrayHasKey('params', $this->currentViewConfiguration);
        Assertion::assertArrayHasKey($parameterName, $this->currentViewConfiguration['params']);
        Assertion::assertEquals($parameterValue, $this->currentViewConfiguration['params'][$parameterName]);
    }

    /**
     * @When /^a content matching the blog configuration is rendered$/
     */
    public function aContentMatchingTheBlogConfigurationIsRendered()
    {
        $this->navigationContext->iVisitLocation(
            $this->locationService->loadLocation(self::BLOG_LOCATION_ID)
        );
    }
}
