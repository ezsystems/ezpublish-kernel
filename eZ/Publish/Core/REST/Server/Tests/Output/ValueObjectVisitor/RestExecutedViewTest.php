<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;
use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values\Content;
use eZ\Publish\Core\REST\Server\Values\RestExecutedView;
use eZ\Publish\Core\Repository\Values\Content as ApiValues;

class RestExecutedViewTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RestExecutedView visitor.
     *
     * @return \DOMDocument
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $view = new RestExecutedView(
            [
                'identifier' => 'test_view',
                'searchResults' => new SearchResult([
                    'searchHits' => [
                        $this->buildContentSearchHit(),
                        $this->buildLocationSearchHit(),
                    ],
                ]),
            ]
        );

        $this->addRouteExpectation(
            'ezpublish_rest_views_load',
            ['viewId' => $view->identifier],
            "/content/views/{$view->identifier}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_views_load_results',
            ['viewId' => $view->identifier],
            "/content/views/{$view->identifier}/results"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $view
        );

        $result = $generator->endDocument(null);

        $this->assertNotNull($result);

        $dom = new \DOMDocument();
        $dom->loadXml($result);

        return $dom;
    }

    public function provideXpathAssertions()
    {
        return [
            ['/View'],
            ['/View[@media-type="application/vnd.ez.api.View+xml"]'],
            ['/View[@href="/content/views/test_view"]'],
            ['/View/identifier'],
            ['/View/identifier[text()="test_view"]'],
            ['/View/Query'],
            ['/View/Query[@media-type="application/vnd.ez.api.Query+xml"]'],
            ['/View/Result'],
            ['/View/Result[@media-type="application/vnd.ez.api.ViewResult+xml"]'],
            ['/View/Result[@href="/content/views/test_view/results"]'],
            ['/View/Result/searchHits/searchHit[@score="0.123" and @index="alexandria"]'],
            ['/View/Result/searchHits/searchHit[@score="0.234" and @index="waze"]'],
        ];
    }

    /**
     * @param string $xpath
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     * @dataProvider provideXpathAssertions
     */
    public function testGeneratedXml($xpath, \DOMDocument $dom)
    {
        $this->assertXPath($dom, $xpath);
    }

    /**
     * Get the Relation visitor.
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestExecutedView
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\RestExecutedView(
            $this->getLocationServiceMock(),
            $this->getContentServiceMock(),
            $this->getContentTypeServiceMock()
        );
    }

    /**
     * @return \eZ\Publish\API\Repository\LocationService|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getLocationServiceMock()
    {
        return $this->getMock('eZ\\Publish\\API\\Repository\\LocationService');
    }

    /**
     * @return \eZ\Publish\API\Repository\ContentService|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getContentServiceMock()
    {
        return $this->getMock('eZ\\Publish\\API\\Repository\\ContentService');
    }

    /**
     * @return \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getContentTypeServiceMock()
    {
        return $this->getMock('eZ\\Publish\\API\\Repository\\ContentTypeService');
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchHit
     */
    protected function buildContentSearchHit()
    {
        return new SearchHit([
            'score' => 0.123,
            'index' => 'alexandria',
            'valueObject' => new ApiValues\Content([
                'versionInfo' => new Content\VersionInfo(['contentInfo' => new ContentInfo()]),
            ]),
        ]);
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchHit
     */
    protected function buildLocationSearchHit()
    {
        return new SearchHit([
            'score' => 0.234,
            'index' => 'waze',
            'valueObject' => new ApiValues\Location(),
        ]);
    }
}
