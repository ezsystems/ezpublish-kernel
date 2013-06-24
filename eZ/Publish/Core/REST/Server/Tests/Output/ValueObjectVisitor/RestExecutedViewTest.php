<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\Repository\Values\Content;
use eZ\Publish\Core\REST\Common;
use eZ\Publish\Core\REST\Server\Values\RestExecutedView;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class RestExecutedViewTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the RestRelation visitor
     *
     * @return string
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

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRelationTag( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'View',
            ),
            $result,
            'Invalid <View> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRelationAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'View',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.View+xml',
                    'href'       => '/content/views/test_view',
                )
            ),
            $result,
            'Invalid <View> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsIdentifierTag( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'identifier',
            ),
            $result,
            'Invalid <identifier> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsIdentifierAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'   => 'identifier',
                'value' => 'test_view'
            ),
            $result,
            'Invalid <identifier> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsQueryTag( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'Query',
            ),
            $result,
            'Invalid <Query> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsQueryAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'   => 'Query',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Query+xml'
                )
            ),
            $result,
            'Invalid <Query> tag attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsResultTag( $result )
    {
        $this->assertTag(
            array(
                'tag' => 'Query',
            ),
            $result,
            'Invalid <Result> tag.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsResultAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'   => 'Result',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.ViewResult+xml+xml',
                    'href'       => '/content/views/test_view/results'
                ),
                'children' => 1
            ),
            $result,
            'Invalid <Result> tag attributes.',
            false
        );
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
