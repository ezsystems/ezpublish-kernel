<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use PHPUnit_Framework_Assert as Assertion;

class ViewConfigurationContext implements Context, SnippetAcceptingContext
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var array
     */
    private $currentViewConfiguration;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @Given /^there is a "([^"]*)" ([a-z0-9_]+) configuration for the "([^"]*)" viewType$/
     */
    public function thereIsAQueryTypeViewConfigurationForTheViewTypeWithTheTemplate($viewConfigName, $what, $viewType)
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
     * @Given /^that configuration matches the QueryType name "([^"]*)"$/
     */
    public function configurationHasMatchOnQueryTypeName($queryTypeName)
    {
        $this->configurationHasMatch('QueryType\Name', $queryTypeName);
    }

    /**
     * @Given /^that configuration matches the QueryType parameter "([^"]*)" with the value "([^"]*)"$/
     */
    public function configurationHasMatchOnQueryTypeParameter($queryTypeParameterName, $queryTypeParameterValue)
    {
        $this->configurationHasMatch('QueryType\Parameters', [$queryTypeParameterName => $queryTypeParameterValue]);
    }

    /**
     * @todo This needs to go either in the QueryTypeContext or a new QueryTypeViewConfigurationContext (that would
     * inherit from ViewConfigurationContext, and interact with the QueryType one).
     *
     * @Given /^that a query_type view that matches on a QueryType and has enable_pager set to false$/
     */
    public function thatAQuery_typeViewThatMatchesOnAQueryTypeAndHasEnable_pagerSetToFalse()
    {
        throw new PendingException();
    }

    /**
     * Finds and returns the $type (query_type_view, content_view) ViewConfiguration with $identifier.
     *
     * @param string $type query_type_view, content_view...
     * @param string $viewType full, line...
     * @param string $identifier the config block key
     *
     * @return array The view configuration
     */
    public function thereIsAViewConfiguration($type, $viewType, $identifier)
    {
        Assertion::assertTrue($this->configResolver->hasParameter($type));

        $typeConfig = $this->configResolver->getParameter($type);
        Assertion::assertArrayHasKey($viewType, $typeConfig);
        Assertion::assertArrayHasKey($identifier, $typeConfig[$viewType]);

        return $typeConfig[$viewType][$identifier];
    }
}
