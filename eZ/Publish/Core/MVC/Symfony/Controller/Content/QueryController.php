<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\API\Repository\SearchService;
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
     */
    public function content($queryTypeName, $viewType, array $parameters = [])
    {
        $query = $this
            ->queryTypeRegistry->getQueryType($queryTypeName)
            ->getQuery($parameters);

        $searchResults = $this->searchService->findContent($query);

        $response = new Response();
        $response->setContent(
            $this->renderer->render($searchResults, $viewType, $parameters)
        );
    }

    /**
     * Runs a findLocation search.
     *
     * @param string $queryTypeName
     * @param string $viewType
     * @param array $parameters
     */
    public function location($queryTypeName, $viewType, array $parameters = [])
    {
        $queryType = $this->queryTypeRegistry->getQueryType($queryTypeName);
        $query = $queryType->getQuery($parameters);

        $queryResult = new QueryTypeResult(
            [
                'searchResult' => $this->searchService->findLocations($query),
                'queryTypeName' => $queryTypeName,
                'query' => $query,
                'parameters' => $parameters,
            ]
        );

        $response = new Response();
        $response->setContent(
            $this->renderer->render(
                $queryResult,
                $viewType,
                $parameters
            )
        );
    }

    /**
     * Runs a findContentInfo search.
     *
     * @param $queryTypeName
     * @param $viewType
     * @param array $parameters
     */
    public function contentInfo($queryTypeName, $viewType, array $parameters = [])
    {
        $query = $this
            ->queryTypeRegistry->getQueryType($queryTypeName)
            ->getQuery($parameters);

        $searchResult = $this->searchService->findContent($query);

        $response = new Response();
        $response->setContent(
            $this->renderingDispatcher->dispatchRendering(
                'content_query',
                $searchResult,
                $viewType,
                $parameters
            )
        );
    }
}
