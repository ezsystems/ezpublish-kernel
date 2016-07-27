<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\QueryType\ContentViewQueryTypeMapper;

/**
 * A content view controller that runs queries based on the matched view configuration.
 *
 * The action used depends on which type of search is needed: location, content or contentInfo.
 */
class QueryController
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\Core\QueryType\ContentViewQueryTypeMapper */
    private $contentViewQueryTypeMapper;

    public function __construct(
        ContentViewQueryTypeMapper $contentViewQueryTypeMapper,
        SearchService $searchService
    ) {
        $this->contentViewQueryTypeMapper = $contentViewQueryTypeMapper;
        $this->searchService = $searchService;
    }

    /**
     * Runs a content search.
     *
     * @param ContentView $view
     * @return ContentView
     */
    public function contentQueryAction(ContentView $view)
    {
        $this->runQuery($view, 'findContent');

        return $view;
    }

    /**
     * Runs a location search.
     *
     * @param ContentView $view
     * @return ContentView
     */
    public function locationQueryAction(ContentView $view)
    {
        $this->runQuery($view, 'findLocations');

        return $view;
    }

    /**
     * Runs a contentInfo search.
     *
     * @param ContentView $view
     * @return ContentView
     */
    public function contentInfoQueryAction(ContentView $view)
    {
        $this->runQuery($view, 'findContentInfo');

        return $view;
    }

    /**
     * Runs the Query defined in $view using $method on SearchService.
     *
     * @param ContentView $view
     * @param string $method Name of the SearchService method to run.
     */
    private function runQuery(ContentView $view, $method)
    {
        $searchResults = $this->searchService->$method(
            $this->contentViewQueryTypeMapper->map($view)
        );
        $view->addParameters([$view->getParameter('query')['assign_results_to'] => $searchResults]);
    }
}
