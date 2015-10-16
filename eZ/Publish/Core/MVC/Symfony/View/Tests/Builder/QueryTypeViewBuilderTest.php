<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Tests\Builder;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\MVC\Symfony\View\Builder\QueryTypeViewBuilder;
use eZ\Publish\Core\MVC\Symfony\View\QueryTypeView;

class QueryTypeViewBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \eZ\Publish\Core\MVC\Symfony\View\ParametersInjector|\PHPUnit_Framework_MockObject_MockObject */
    private $injectorMock;

    /** @var \eZ\Publish\Core\QueryType\QueryTypeRegistry|\PHPUnit_Framework_MockObject_MockObject */
    private $registryMock;

    /** @var \eZ\Publish\API\Repository\SearchService|\PHPUnit_Framework_MockObject_MockObject */
    private $searchServiceMock;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Builder\QueryTypeViewBuilder */
    private $builder;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Configurator|\PHPUnit_Framework_MockObject_MockObject */
    private $viewConfiguratorMock;

    public function setUp()
    {
        $this->registryMock = $this->getMock('eZ\Publish\Core\QueryType\QueryTypeRegistry');
        $this->searchServiceMock = $this->getMock('eZ\Publish\API\Repository\SearchService');
        $this->injectorMock = $this->getMock('eZ\Publish\Core\MVC\Symfony\View\ParametersInjector');
        $this->viewConfiguratorMock = $this->getMock('eZ\Publish\Core\MVC\Symfony\View\Configurator');
        $this->builder = new QueryTypeViewBuilder(
            $this->registryMock,
            $this->searchServiceMock,
            $this->viewConfiguratorMock,
            $this->injectorMock
        );
    }

    public function testMatches()
    {
        self::assertTrue($this->builder->matches('ez_query:content'));
        self::assertTrue($this->builder->matches('ez_query:location'));
        self::assertTrue($this->builder->matches('ez_query:contentInfo'));
        self::assertFalse($this->builder->matches('any:foo'));
    }

    /**
     * @dataProvider builderParametersProvider
     */
    public function testBuild($controller, $query, $expectedSearchedType, $searchMethod)
    {
        $parameters = ['_controller' => $controller, 'queryTypeName' => 'latest_articles', 'viewType' => 'full'];

        $queryTypeMock = $this->getMock('eZ\Publish\Core\QueryType\QueryType');
        $queryTypeMock
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->registryMock
            ->expects($this->once())
            ->method('getQueryType')
            ->with('latest_articles')
            ->will($this->returnValue($queryTypeMock));

        $view = $this->builder->buildView($parameters);

        self::assertEquals('latest_articles', $view->getQueryTypeName());
        self::assertInstanceOf('Pagerfanta\Pagerfanta', $view->getSearchResult());
        self::assertEquals($expectedSearchedType, $view->getSearchedType());
    }

    public function builderParametersProvider()
    {
        return [
            ['ez_query:content', new Query(), QueryTypeView::SEARCH_TYPE_CONTENT, 'findContent'],
            ['ez_query:contentInfo', new Query(), QueryTypeView::SEARCH_TYPE_CONTENT_INFO, 'findContentInfo'],
            ['ez_query:location', new LocationQuery(), QueryTypeView::SEARCH_TYPE_LOCATION, 'findLocations'],
        ];
    }
}
