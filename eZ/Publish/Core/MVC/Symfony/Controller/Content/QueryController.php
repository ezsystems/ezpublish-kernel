<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\MVC\Symfony\Controller\Controller;
use eZ\Publish\Core\MVC\Symfony\View\QueryTypeResult;
use eZ\Publish\Core\MVC\Symfony\View\ViewRenderer;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use Symfony\Component\HttpFoundation\Response;

class QueryController extends Controller
{
    /** @var \eZ\Publish\Core\QueryType\QueryTypeRegistry */
    private $queryTypeRegistry;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\ViewRenderer */
    private $renderer;

    public function __construct(QueryTypeRegistry $queryTypeRegistry, SearchService $searchService, ViewRenderer $renderer)
    {
        $this->queryTypeRegistry = $queryTypeRegistry;
        $this->searchService = $searchService;
        $this->renderer = $renderer;
    }

    /**
     * Runs a findContent search.
     *
     * @param $queryTypeName
     * @param $viewType
     * @param array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function content($queryTypeName, $viewType, array $parameters = [])
    {
        return $this->buildResponse(
            $queryTypeName,
            $viewType,
            $parameters,
            function(Query $query) { return $this->searchService->findContent($query); }
        );
    }

    /**
     * Runs a findLocation search.
     *
     * @param string $queryTypeName
     * @param string $viewType
     * @param array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function location($queryTypeName, $viewType, array $parameters = [])
    {
        return $this->buildResponse(
            $queryTypeName,
            $viewType,
            $parameters,
            function(Query $query) { return $this->searchService->findLocations($query); }
        );
    }

    /**
     * Runs a findContentInfo search.
     *
     * @param string $queryTypeName
     * @param string $viewType
     * @param array $parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentInfo($queryTypeName, $viewType, array $parameters = [])
    {
        return $this->buildResponse(
            $queryTypeName,
            $viewType,
            $parameters,
            function(Query $query) { return $this->searchService->findContentInfo($query); }
        );
    }

    /**
     * @param string $queryTypeName
     * @param string $viewType
     * @param array $parameters
     * @param callable $searchCallback Will be called to run the actual search, with the Query as the 1st parameter
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function buildResponse($queryTypeName, $viewType, array $parameters = [], Callable $searchCallback)
    {
        $queryType = $this->queryTypeRegistry->getQueryType($queryTypeName);
        $query = $queryType->getQuery($parameters);

        $queryResult = new QueryTypeResult(
            [
                'searchResult' => $searchCallback($query),
                'queryTypeName' => $queryTypeName,
                'query' => $query,
                'parameters' => $parameters,
            ]
        );

        $response = new Response();
        $response->setContent($this->renderer->render($queryResult, $viewType, $parameters));
        return $response;
    }
}
