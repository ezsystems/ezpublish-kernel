<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Tests\ParametersInjector;

use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\MVC\Symfony\View\Event\ViewParametersFilterEvent;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector\QueryTypeResults;
use eZ\Publish\Core\MVC\Symfony\View\QueryTypeView;

class QueryTypeResultsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ParametersInjector\QueryTypeResults
     */
    private $injector;

    public function setUp()
    {
        $this->injector = new QueryTypeResults();
    }

    public function testInjectCommonParameters()
    {
        $view = new QueryTypeView();
        $view->setSearchResult(
            new SearchResult([
                'totalCount' => 42,
                'searchHits' => [new SearchHit(), new SearchHit()]
            ])
        );
        $view->setSearchedType(QueryTypeView::SEARCH_TYPE_CONTENT_INFO);
        $view->setQueryParameters(['foo' => 'bar']);
        $event = new ViewParametersFilterEvent($view, []);

        $this->injector->injectQueryTypeResults($event);

        $parameterBag = $event->getParameterBag();
        self::assertEquals(42, $parameterBag->get('total_count'));
        self::assertEquals(['foo' => 'bar'], $parameterBag->get('parameters'));
        self::assertEquals(2, $parameterBag->get('list_count'));
    }

    public function testInjectLocationList()
    {
        $view = new QueryTypeView();
        $view->setSearchResult(
            new SearchResult([
                'searchHits' => [new SearchHit(), new SearchHit()]
            ])
        );
        $view->setSearchedType(QueryTypeView::SEARCH_TYPE_LOCATION);
        $view->setQueryParameters(['foo' => 'bar']);
        $event = new ViewParametersFilterEvent($view, []);

        $this->injector->injectQueryTypeResults($event);

        $parameterBag = $event->getParameterBag();
        self::assertTrue($parameterBag->has('location_list'));
    }

    public function testInjectContentInfoList()
    {
        $view = new QueryTypeView();
        $view->setSearchResult(
            new SearchResult([
                'searchHits' => [new SearchHit(), new SearchHit()]
            ])
        );
        $view->setSearchedType(QueryTypeView::SEARCH_TYPE_CONTENT_INFO);
        $view->setQueryParameters(['foo' => 'bar']);
        $event = new ViewParametersFilterEvent($view, []);

        $this->injector->injectQueryTypeResults($event);

        $parameterBag = $event->getParameterBag();
        self::assertTrue($parameterBag->has('content_info_list'));
    }

    public function testInjectContentList()
    {
        $view = new QueryTypeView();
        $view->setSearchResult(
            new SearchResult([
                'searchHits' => [new SearchHit(), new SearchHit()]
            ])
        );
        $view->setSearchedType(QueryTypeView::SEARCH_TYPE_CONTENT);
        $view->setQueryParameters(['foo' => 'bar']);
        $event = new ViewParametersFilterEvent($view, []);

        $this->injector->injectQueryTypeResults($event);

        $parameterBag = $event->getParameterBag();
        self::assertTrue($parameterBag->has('content_list'));
    }
}
