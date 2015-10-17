<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;

class QueryController
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\Core\QueryType\QueryTypeRegistry */
    private $queryTypeRegistry;

    public function __construct(QueryTypeRegistry $queryTypeRegistry, SearchService $searchService)
    {
        $this->queryTypeRegistry = $queryTypeRegistry;
        $this->searchService = $searchService;
    }

    public function contentAction(ContentView $view)
    {
        $searchResults = $this->searchService->findContent(
            $this->getQuery($view),
            [] // @todo we most likely want to use the Viewed content language(s)
        );
        $variableName = $view->hasParameter('variable') ? $view->getParameter('variable') : 'content_list';
        $view->addParameters([$variableName => $searchResults]);

        return $view;
    }

    public function locationAction(ContentView $view)
    {
        return $view;
    }

    public function contentInfoAction(ContentView $view)
    {
        return $view;
    }

    /**
     * Returns the query configured in the view parameters
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentView $view
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If the query parameter isn't set
     */
    private function getQuery(ContentView $view)
    {
        if (!$view->hasParameter('query')) {
            throw new InvalidArgumentException('query', 'Missing required query parameter');
        }
        $queryType = $this->queryTypeRegistry->getQueryType($view->getParameter('queryType'));

        $queryParameters = $view->hasParameter('queryParameters') ? $view->getParameter('query_parameters') : [];
        $supportedQueryParameters = array_flip($queryType->getSupportedParameters());
        foreach (array_keys($queryParameters) as $queryParameterName) {
            if (!isset( $supportedQueryParameters[$queryParameterName] )) {
                throw new InvalidArgumentException('query parameters',
                    "unsupported query parameter $queryParameterName");
            }
        }

        return $queryType->getQuery($queryParameters);
}
}
