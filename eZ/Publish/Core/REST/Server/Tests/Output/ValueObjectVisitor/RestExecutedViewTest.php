<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\Core\REST\Server\Values\RestExecutedView;

class RestExecutedViewTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RestRelation visitor
     *
     * @return \DOMDocument
     */
    public function testVisit()
    {
        $visitor   = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $view = new RestExecutedView(
            array(
                'identifier'    => 'test_view',
                'searchResults' => new SearchResult,
            )
        );

        $this->addRouteExpectation(
            'ezpublish_rest_loadView',
            array( 'viewId' => $view->identifier ),
            "/content/views/{$view->identifier}"
        );
        $this->addRouteExpectation(
            'ezpublish_rest_loadViewResults',
            array( 'viewId' => $view->identifier ),
            "/content/views/{$view->identifier}/results"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $view
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        $dom = new \DOMDocument();
        $dom->loadXml( $result );

        return $dom;
    }

    public function provideXpathAssertions()
    {
        return array(
            array( '/View' ),
            array( '/View[@media-type="application/vnd.ez.api.View+xml"]' ),
            array( '/View[@href="/content/views/test_view"]' ),
            array( '/View/identifier' ),
            array( '/View/identifier[text()="test_view"]' ),
            array( '/View/Query' ),
            array( '/View/Query[@media-type="application/vnd.ez.api.Query+xml"]' ),
            array( '/View/Result' ),
            array( '/View/Result[@media-type="application/vnd.ez.api.ViewResult+xml"]' ),
            array( '/View/Result[@href="/content/views/test_view/results"]' ),
        );
    }

    /**
     * @param string $xpath
     * @param \DOMDocument $dom
     *
     * @depends testVisit
     * @dataProvider provideXpathAssertions
     */
    public function testGeneratedXml( $xpath, \DOMDocument $dom )
    {
        $this->assertXPath( $dom, $xpath );
    }

    /**
     * Get the Relation visitor
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
        return $this->getMock( 'eZ\\Publish\\API\\Repository\\LocationService' );
    }

    /**
     * @return \eZ\Publish\API\Repository\ContentService|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getContentServiceMock()
    {
        return $this->getMock( 'eZ\\Publish\\API\\Repository\\ContentService' );
    }

    /**
     * @return \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getContentTypeServiceMock()
    {
        return $this->getMock( 'eZ\\Publish\\API\\Repository\\ContentTypeService' );
    }
}
