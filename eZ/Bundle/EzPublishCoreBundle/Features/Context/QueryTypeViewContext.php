<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkAwareContext;
use EzSystems\BehatBundle\Context\Browser\MinkTrait;
use PHPUnit_Framework_Assert as Assertion;

class QueryTypeViewContext implements Context, SnippetAcceptingContext, MinkAwareContext
{
    use MinkTrait;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\Features\Context\ViewConfigurationContext
     */
    private $viewConfigurationContext;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\Features\Context\NavigationContext
     */
    private $navigationContext;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\Features\Context\QueryTypeContext
     */
    private $queryTypeContext;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->viewConfigurationContext = $environment->getContext('eZ\Bundle\EzPublishCoreBundle\Features\Context\ViewConfigurationContext');
        $this->navigationContext = $environment->getContext('eZ\Bundle\EzPublishCoreBundle\Features\Context\NavigationContext');
        $this->queryTypeContext = $environment->getContext('eZ\Bundle\EzPublishCoreBundle\Features\Context\QueryTypeContext');
    }

    /**
     * @Given /^that a query_type view has enable_pager set to false$/
     */
    public function aQueryTypeViewHasEnablePagerSetToFalse()
    {
        $viewConfiguration = $this->viewConfigurationContext
            ->thereIsAViewConfiguration('query_type_view', 'full', 'pager_disabled');

        Assertion::assertArrayHasKey('enable_pager', $viewConfiguration);
        Assertion::assertFalse($viewConfiguration['enable_pager']);

        $this->navigationContext->thereIsARoute('ez_platform_behat_views_query_type_pager_disabled');
    }

    /**
     * @Given /^that a query_type view does not specify enable_pager$/
     */
    public function aQueryTypeViewDoesNotSpecifyEnablePager()
    {
        $viewConfiguration = $this->viewConfigurationContext
            ->thereIsAViewConfiguration('query_type_view', 'full', 'pager_enabled');

        Assertion::assertArrayHasKey('enable_pager', $viewConfiguration);
        // If enable_pager is not specified, it is set to true by default
        Assertion::assertTrue($viewConfiguration['enable_pager']);

        $this->navigationContext->thereIsARoute('ez_platform_behat_views_query_type_pager_enabled');
    }

    /**
     * @When /^that view is rendered$/
     */
    public function thatViewIsRendered()
    {
        $this->navigationContext->iGoToThatRoute();
    }

    /**
     * @Then /^the query results are assigned to the template as an array$/
     */
    public function theQueryResultsAreAssignedToTheTemplateAsAnArray()
    {
        $this->assertSession()->elementExists('css', 'div#assertion-result-type-array.true');
    }

    /**
     * @Then /^the query results are assigned to the template as a PagerFanta Pager object$/
     */
    public function theQueryResultsAreAssignedToTheTemplateAsAPagerFantaPagerObject()
    {
        $this->assertSession()->elementExists('css', 'div#assertion-result-type-pager.true');
    }

    /**
     * @Given /^that a query_type view matches on a QueryType name$/
     */
    public function aQueryTypeViewMatchesOnQueryTypeName()
    {
        $viewConfiguration = $this->viewConfigurationContext
            ->thereIsAViewConfiguration('query_type_view', 'full', 'match_name');

        Assertion::assertArrayHasKey('match', $viewConfiguration);
        Assertion::assertArrayHasKey('QueryType\Name', $viewConfiguration['match']);

        $this->queryTypeContext->setCurrentQueryTypeByName($viewConfiguration['match']['QueryType\Name']);
        $this->navigationContext->thereIsARoute('ez_platform_behat_views_query_type_match_name');
    }

    /**
     * @When /^ez_query:content is rendered with that QueryType name$/
     */
    public function ezQueryContentIsRenderedWithThatQueryTypeName()
    {
        $this->navigationContext->thereIsARoute('ez_platform_behat_views_query_type_match_name');
        $this->navigationContext->iGoToThatRoute();
    }

    /**
     * @Then /^the search results from that QueryType are assigned to the template as content_list$/
     */
    public function theSearchResultsFromThatQueryTypeAreAssignedToTheTemplateAsContentList()
    {
        $searchResult = $this->queryTypeContext->runContentQuery();

        // the pager limit
        $limit = 10;
        $i = 1;
        foreach ($searchResult->searchHits as $item) {
            if (++$i == $limit) {
                break;
            }
            $this->assertSession()->elementExists('css', "li#content-id-{$item->valueObject->id}");
        }
    }
}
