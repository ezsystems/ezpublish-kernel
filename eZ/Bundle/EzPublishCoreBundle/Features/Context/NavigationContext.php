<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkAwareContext;
use EzSystems\BehatBundle\Context\Browser\MinkTrait;
use PHPUnit_Framework_Assert as Assertion;
use Symfony\Component\Routing\RouterInterface;

class NavigationContext implements Context, SnippetAcceptingContext, MinkAwareContext
{
    use MinkTrait;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Symfony\Component\Routing\Route
     */
    private $currentRoute;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @Given /^there is a route "([^"]*)"$/
     */
    public function thereIsARoute($routeIdentifier)
    {
        /** @var \Symfony\Component\Routing\RouterInterface $router */
        $routeCollection = $this->router->getRouteCollection();
        Assertion::assertNotNull(
            $route = $routeCollection->get($routeIdentifier),
            "Failed asserting that there is a route named $routeIdentifier"
        );

        $this->currentRoute = $route;
    }

    /**
     * @Given /^that route has the default "([^"]*)" set to "([^"]*)"$/
     */
    public function routeHasTheDefaultSetTo($defaultName, $defaultValue)
    {
        Assertion::assertNotNull($this->currentRoute, "No currentRoute was set");

        Assertion::assertTrue(
            $this->currentRoute->hasDefault($defaultName),
            "Failed asserting that the route has the default attribute 'queryTypeName'"
        );

        if (is_string($defaultValue)) {
            Assertion::assertEquals(
                $defaultValue,
                $this->currentRoute->getDefault($defaultName),
                "Failed asserting that the route has the default attribute '$defaultName' set to '$defaultValue'"
            );
        } elseif (is_array($defaultValue)) {
            Assertion::assertArraySubset(
                $defaultValue,
                $this->currentRoute->getDefault($defaultName),
                "Failed asserting that the route has the default attribute '$defaultName' with the given array items"
            );
        }
    }

    /**
     * @Given /^that route has the default "([^"]*)" set to an array with the key "([^"]*)" set to "([^"]*)"$/
     */
    public function routeHasTheDefaultSetToArray($defaultName, $arrayKey, $arrayValue)
    {
        $this->routeHasTheDefaultSetTo($defaultName, [$arrayKey => $arrayValue]);
    }

    /**
     * @When /^I go to that route$/
     */
    public function iGoToThatRoute()
    {
        Assertion::assertTrue(isset($this->currentRoute), "No current Route was set");
        $this->getSession()->visit($this->currentRoute->getPath());
    }

}
