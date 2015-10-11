<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\View\Event\ViewParametersFilterEvent;
use eZ\Publish\Core\MVC\Symfony\View\Events;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;
use eZ\Publish\Core\MVC\Symfony\View\QueryTypeView;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class QueryTypeResults implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [Events::VIEW_PARAMETERS_INJECTION => 'injectQueryTypeResults'];
    }

    public function injectQueryTypeResults(ViewParametersFilterEvent $event)
    {
        $view = $event->getView();
        if (!$view instanceof QueryTypeView) {
            return;
        }

        $searchResult = $view->getSearchResult();
        $parameters = [
            'total_count' => $searchResult->totalCount,
            'parameters' => $view->getQueryParameters(),
            'list_count' => count($searchResult->searchHits)
        ] + $this->extractSearchResults($view);

        $event->getParameterBag()->add($parameters);
    }

    /**
     * Extracts the search results into a $key + value objects array, with $key depending on the search type
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
            throw new InvalidArgumentException('searchedType', "Unknown search type " . $view->getSearchedType());
        }
        $itemsKey = $searchedTypeListKeyMap[$view->getSearchedType()];

        $items = [];
        foreach ($view->getSearchResult()->searchHits as $searchHit) {
            $items[] = $searchHit->valueObject;
        }

        return [$itemsKey => $items];
    }
}
