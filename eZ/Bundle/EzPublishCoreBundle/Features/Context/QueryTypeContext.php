<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use EzSystems\BehatBundle\Context\Browser\MinkTrait;
use PHPUnit_Framework_Assert as Assertion;
use Symfony\Component\HttpKernel\KernelInterface;

class QueryTypeContext implements Context, SnippetAcceptingContext, KernelAwareContext, MinkAwareContext
{
    use MinkTrait;

    /** @var KernelInterface */
    private $kernel;

    /**
     * @var \eZ\Publish\Core\QueryType\QueryType
     */
    private $currentQueryType;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    private $searchResult;

    /**
     * @var \eZ\Publish\Core\QueryType\QueryTypeRegistry
     */
    private $queryTypeRegistry;

    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    private $searchService;

    public function __construct( QueryTypeRegistry $queryTypeRegistry, SearchService $searchService)
    {
        $this->queryTypeRegistry = $queryTypeRegistry;
        $this->searchService = $searchService;
    }

    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    private function getKernel()
    {
        return $this->kernel;
    }

    /**
     * @Given /^that there is a QueryType "([^"]*)" with a "([^"]*)" parameter$/
     */
    public function thatThereIsAQueryTypeWithAParameter($queryTypeName, $parameterName)
    {
        Assertion::assertInstanceOf(
            'eZ\Publish\Core\QueryType\QueryType',
            $queryType = $this->queryTypeRegistry->getQueryType($queryTypeName),
            "Failed asserting that a QueryType named $queryTypeName exists."
        );
        Assertion::assertTrue(
            in_array($parameterName, $queryType->getSupportedParameters()),
            "Failed asserting that the QueryType $queryTypeName has a parameter $parameterName"
        );
        $this->currentQueryType = $queryType;
    }

    /**
     * @Given /^running a content search from that QueryType with the parameter "([^"]*)" set to "([^"]*)" returns search results$/
     */
    public function thatQueryTypeWithTheParameterSetToReturnsSearchResults($parameterName, $parameterValue)
    {
        Assertion::assertTrue(
            isset($this->currentQueryType),
            "No current QueryType was set"
        );

        $query = $this->currentQueryType->getQuery([$parameterName => $parameterValue]);

        $this->searchResult = $this->searchService->findContent($query);
        Assertion::assertGreaterThan(0, $this->searchResult->totalCount);
    }

    /**
     * @Then /^I see the QueryType results listed with this template$/
     */
    public function iSeeTheQueryTypeResultsListedWithThisTemplate()
    {
        $page = $this->getSession()->getPage();
        $articles = $page->findAll('css', 'li.article');
    }
}
