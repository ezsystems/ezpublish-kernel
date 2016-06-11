<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\QueryType\ContentViewQueryTypeMapper;

class QueryController
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var ContentViewQueryTypeMapper */
    private $contentViewQueryTypeMapper;

    public function __construct(
        ContentViewQueryTypeMapper $contentViewQueryTypeMapper,
        SearchService $searchService
    ) {
        $this->contentViewQueryTypeMapper = $contentViewQueryTypeMapper;
        $this->searchService = $searchService;
    }

    public function contentQueryAction(ContentView $view)
    {
        $this->runQuery($view, 'findContent');

        return $view;
    }

    public function locationQueryAction(ContentView $view)
    {
        $this->runQuery($view, 'findLocations');

        return $view;
    }

    public function contentInfoQueryAction(ContentView $view)
    {
        $this->runQuery($view, 'findContentInfo');

        return $view;
    }

    /**
     * @param ContentView $view
     */
    public function runQuery(ContentView $view, $method)
    {
        $searchResults = $this->searchService->$method(
            $this->contentViewQueryTypeMapper->map($view)
        );
        $view->addParameters([$view->getParameter('variable') => $searchResults]);
    }
}
