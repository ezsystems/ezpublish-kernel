<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Builder;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\View\Configurator;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;
use eZ\Publish\Core\MVC\Symfony\View\QueryTypeView;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use Pagerfanta\Pagerfanta;

class QueryTypeViewBuilder implements ViewBuilder
{
    /**
     * @var \eZ\Publish\Core\QueryType\QueryTypeRegistry
     */
    private $queryTypeRegistry;

    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    private $searchService;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ParametersInjector
     */
    private $viewParametersInjector;

    public function __construct(
        QueryTypeRegistry $queryTypeRegistry,
        SearchService $searchService,
        Configurator $viewConfigurator,
        ParametersInjector $viewParametersInjector
    ) {
        $this->queryTypeRegistry = $queryTypeRegistry;
        $this->searchService = $searchService;
        $this->viewConfigurator = $viewConfigurator;
        $this->viewParametersInjector = $viewParametersInjector;
    }

    public function matches($argument)
    {
        return strpos($argument, 'ez_query:') !== false;
    }

    /**
     * Builds the View based on $parameters.
     *
     * @param array $parameters
     *
     * @return QueryTypeView
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function buildView(array $parameters)
    {
        $view = new QueryTypeView(null, [], $parameters['viewType']);

        if (!isset($parameters['queryTypeName'])) {
            throw new InvalidArgumentException('queryTypeName', 'QueryTypeName not specified');
        }
        $view->setQueryTypeName($parameters['queryTypeName']);

        $view->setQueryParameters(isset($parameters['parameters']) ? $parameters['parameters'] : []);
        $view->setQuery(
            $this->queryTypeRegistry->getQueryType($parameters['queryTypeName'])->getQuery(
                $view->getQueryParameters()
            )
        );

        $this->viewConfigurator->configure($view);

        $view->setSearchedType(
            $this->extractSearchedType($parameters['_controller'])
        );

        $configHash = $view->getConfigHash();
        if (isset($configHash['enable_pager']) && $configHash['enable_pager'] === false) {
            $view->setSearchResult(
                $this->runQuery($view->getQuery(), $view->getSearchedType())
            );
        } else {
            $view->setSearchResult(
                $this->createPager($view->getQuery(), $view->getSearchedType())
            );
        }

        $this->viewParametersInjector->injectViewParameters($view, $parameters);

        return $view;
    }

    /**
     * Runs the query using the relevant SearchService method, based on the controller string.
     *
     * @param Query|LocationQuery $query
     * @param string $searchedType
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     * @internal param string $controllerString ez_query:(content|location|contentInfo)
     */
    private function runQuery($query, $searchedType)
    {
        $searchMethod = $this->getSearchMethod($searchedType);

        return $this->searchService->$searchMethod($query);
    }

    /**
     * Creates the PagerFanta instance for a query and searched type.
     *
     * @param $query
     * @param $searchedType
     *
     * @return \Pagerfanta\Pagerfanta
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    private function createPager($query, $searchedType)
    {
        $searchMethod = $this->getSearchMethod($searchedType);
        $searchService = $this->searchService;

        return new PagerFanta(
            new QueryTypeSearchAdapter(
                $query,
                function (Query $query) use ($searchService, $searchMethod) {
                    return $searchService->$searchMethod($query);
                }
            )
        );
    }

    /**
     * Extracts the searched type (one of the QueryType::SEARCH_TYPE_* constants) from the controller string.
     *
     * @param string $controllerString
     *
     * @return string
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    private function extractSearchedType($controllerString)
    {
        $actionSearchedTypeMap = [
            'location' => QueryTypeView::SEARCH_TYPE_LOCATION,
            'content' => QueryTypeView::SEARCH_TYPE_CONTENT,
            'contentInfo' => QueryTypeView::SEARCH_TYPE_CONTENT_INFO,
        ];
        list(, $action) = explode(':', $controllerString);

        if (!isset($actionSearchedTypeMap[$action])) {
            throw new InvalidArgumentException('Query controller action', 'Unknown query controller action');
        }

        return $actionSearchedTypeMap[$action];
    }

    /**
     * Gets the SearchService method to use based on the searched type.
     *
     * @param string $searchedType
     *
     * @return string
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no method exists for the searched type
     */
    private function getSearchMethod($searchedType)
    {
        $searchedTypeMethodMap = [
            QueryTypeView::SEARCH_TYPE_LOCATION => 'findLocations',
            QueryTypeView::SEARCH_TYPE_CONTENT => 'findContent',
            QueryTypeView::SEARCH_TYPE_CONTENT_INFO => 'findContentInfo',
        ];
        if (!isset($searchedTypeMethodMap[$searchedType])) {
            throw new InvalidArgumentException('search method', "No search method for the searched type $searchedType");
        }

        return $searchedTypeMethodMap[$searchedType];
    }
}
