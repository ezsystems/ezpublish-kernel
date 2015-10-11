<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Builder;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;
use eZ\Publish\Core\MVC\Symfony\View\QueryTypeView;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;

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
        ParametersInjector $viewParametersInjector
    ) {
        $this->queryTypeRegistry = $queryTypeRegistry;
        $this->searchService = $searchService;
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

        $view->setSearchedType(
            $this->extractSearchedType($parameters['_controller'])
        );
        $view->setSearchResult(
            $this->runQuery($view->getQuery(), $view->getSearchedType())
        );

        $this->viewParametersInjector->injectViewParameters($view, $parameters);

        return $view;
    }

    /**
     * Runs the query using the relevant SearchService method, based on the controller string.
     *
     * @param Query|LocationQuery $query
     * @param string $controllerString ez_query:(content|location|contentInfo)
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SearchResult
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If the query controller action couldn't be resolved.
     */
    private function runQuery($query, $searchedType)
    {
        $searchedTypeMethodMap = [
            'location' => 'findLocations',
            'content' => 'findContent',
            'contentInfo' => 'findContentInfo'
        ];
        $searchMethod = $searchedTypeMethodMap[$searchedType];

        return $this->searchService->$searchMethod($query);
    }

    private function extractSearchedType($controllerString)
    {
        $actionSearchedTypeMap = [
            'location' => QueryTypeView::SEARCH_TYPE_LOCATION,
            'content' => QueryTypeView::SEARCH_TYPE_CONTENT,
            'contentInfo' => QueryTypeView::SEARCH_TYPE_CONTENT_INFO
        ];
        list( , $action ) = explode(':', $controllerString);

        if (!isset($actionSearchedTypeMap[$action])) {
            throw new InvalidArgumentException("Query controller action", "Unknown query controller action");
        }

        return $actionSearchedTypeMap[$action];
    }
}
