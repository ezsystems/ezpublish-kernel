<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkAwareContext;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use EzSystems\BehatBundle\Context\Browser\MinkTrait;
use PHPUnit_Framework_Assert as Assertion;

class QueryTypeContext implements Context, SnippetAcceptingContext, MinkAwareContext
{
    use MinkTrait;

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

    public function __construct(QueryTypeRegistry $queryTypeRegistry, SearchService $searchService)
    {
        $this->queryTypeRegistry = $queryTypeRegistry;
        $this->searchService = $searchService;
    }

    /**
     * Sets the current QueryType given a QueryType name.
     *
     * @param string $queryTypeName
     */
    public function setCurrentQueryTypeByName($queryTypeName)
    {
        $this->currentQueryType = $this->queryTypeRegistry->getQueryType($queryTypeName);
    }

    /**
     * @Given /^that a QueryType with that name exists$/
     */
    public function thatAQueryTypeWithThatNameExists()
    {
        // it was already checked when the QueryType was loaded, but you never know
        Assertion::assertInstanceOf('eZ\Publish\Core\QueryType\QueryType', $this->currentQueryType);
    }

    public function runContentQuery()
    {
        return $this->searchService->findContent($this->currentQueryType->getQuery());
    }
}
