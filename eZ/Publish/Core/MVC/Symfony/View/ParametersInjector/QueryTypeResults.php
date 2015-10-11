<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\QueryTypeView;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class QueryTypeResults implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_VIEW_PARAMETERS => 'injectQueryTypeResults'];
    }

    public function injectQueryTypeResults(FilterViewParametersEvent $event)
    {
        $view = $event->getView();
        if (!$view instanceof QueryTypeView) {
            return;
        }

        $searchResult = $view->getSearchResult();
        $parameters = [
            'total_count' => $this->mapTotalCount($searchResult),
            'parameters' => $view->getQueryParameters(),
            'list_count' => $this->mapListCount($searchResult),
        ] + $this->extractSearchResults($view);

        $event->getParameterBag()->add($parameters);
    }

    /**
     * Extracts the search results into a $key + value objects array, with $key depending on the search type.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\QueryTypeView $view
     *
     * @return array resultsKey => resultsArray
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If the searched type is unknown
     */
    private function extractSearchResults(QueryTypeView $view)
    {
        $searchedTypeListKeyMap = [
            QueryTypeView::SEARCH_TYPE_CONTENT_INFO => 'content_info_list',
            QueryTypeView::SEARCH_TYPE_CONTENT => 'content_list',
            QueryTypeView::SEARCH_TYPE_LOCATION => 'location_list',
        ];

        if (!isset($searchedTypeListKeyMap[$view->getSearchedType()])) {
            throw new InvalidArgumentException('searchedType', 'Unknown search type ' . $view->getSearchedType());
        }
        $itemsKey = $searchedTypeListKeyMap[$view->getSearchedType()];

        $searchResult = $view->getSearchResult();

        if ($searchResult instanceof Pagerfanta) {
            return [$itemsKey => $searchResult];
        }

        if ($searchResult instanceof SearchResult) {
            $items = [];
            foreach ($searchResult->searchHits as $searchHit) {
                $items[] = $searchHit->valueObject;
            }

            return [$itemsKey => $items];
        }

        throw new InvalidArgumentException('searchResult', 'Invalid search result type');
    }

    public function mapTotalCount($searchResult)
    {
        if ($searchResult instanceof PagerFanta) {
            return count($searchResult);
        }

        if ($searchResult instanceof SearchResult) {
            return $searchResult->totalCount;
        }

        throw new InvalidArgumentException('searchResult', 'Invalid search result type');
    }

    /**
     * Maps the SearchResult to the number of elements in the current resultset.
     */
    public function mapListCount($searchResult)
    {
        if ($searchResult instanceof PagerFanta) {
            return count($searchResult);
        }

        if ($searchResult instanceof SearchResult) {
            return count($searchResult->searchHits);
        }

        throw new InvalidArgumentException('searchResult', 'Invalid search result type');
    }
}
