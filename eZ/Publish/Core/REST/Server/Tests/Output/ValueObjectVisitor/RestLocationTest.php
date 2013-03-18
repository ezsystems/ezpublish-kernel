<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\RestLocation;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\REST\Common;

class RestLocationTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the Location visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getLocationVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $location = new RestLocation(
            new Location(
                array(
                    'id' => 42,
                    'priority' => 0,
                    'hidden' => false,
                    'invisible' => true,
                    'remoteId' => 'remote-id',
                    'parentLocationId' => 21,
                    'pathString' => '/1/2/21/42/',
                    'depth' => 3,
                    'sortField' => Location::SORT_FIELD_PATH,
                    'sortOrder' => Location::SORT_ORDER_ASC,
                    'contentInfo' => new ContentInfo(
                        array(
                            'id' => 42
                        )
                    )
                )
            ),
            // Dummy value for ChildCount
            0
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $location
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains Location element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Location',
                'children' => array(
                    'count' => 13
                )
            ),
            $result,
            'Invalid <Location> element.',
            false
        );
    }

    /**
     * Test if result contains Location element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Location',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Location+xml',
                    'href'       => '/content/locations/1/2/21/42',
                )
            ),
            $result,
            'Invalid <Location> attributes.',
            false
        );
    }

    /**
     * Test if result contains id value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'id',
                'content'  => '42'
            ),
            $result,
            'Invalid or non-existing <Location> id value element.',
            false
        );
    }

    /**
     * Test if result contains priority value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPriorityValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'priority',
                'content'  => '0'
            ),
            $result,
            'Invalid or non-existing <Location> priority value element.',
            false
        );
    }

    /**
     * Test if result contains hidden value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsHiddenValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'hidden',
                'content'  => 'false'
            ),
            $result,
            'Invalid or non-existing <Location> hidden value element.',
            false
        );
    }

    /**
     * Test if result contains invisible value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsInvisibleValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'invisible',
                'content'  => 'true'
            ),
            $result,
            'Invalid or non-existing <Location> invisible value element.',
            false
        );
    }

    /**
     * Test if result contains remoteId value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRemoteIdValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'remoteId',
                'content'  => 'remote-id'
            ),
            $result,
            'Invalid or non-existing <Location> remoteId value element.',
            false
        );
    }

    /**
     * Test if result contains Children element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsChildrenElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Children'
            ),
            $result,
            'Invalid <Children> element.',
            false
        );
    }

    /**
     * Test if result contains Children element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsChildrenAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Children',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.LocationList+xml',
                    'href'       => '/content/locations/1/2/21/42/children',
                )
            ),
            $result,
            'Invalid <Children> attributes.',
            false
        );
    }

    /**
     * Test if result contains ParentLocation element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsParentLocationElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ParentLocation'
            ),
            $result,
            'Invalid <ParentLocation> element.',
            false
        );
    }

    /**
     * Test if result contains ParentLocation element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsParentLocationAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ParentLocation',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Location+xml',
                    'href'       => '/content/locations/1/2/21',
                )
            ),
            $result,
            'Invalid <ParentLocation> attributes.',
            false
        );
    }

    /**
     * Test if result contains Content element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Content'
            ),
            $result,
            'Invalid <Content> element.',
            false
        );
    }

    /**
     * Test if result contains Content element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Content',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Content+xml',
                    'href'       => '/content/objects/42',
                )
            ),
            $result,
            'Invalid <Content> attributes.',
            false
        );
    }

    /**
     * Test if result contains pathString value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPathStringValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'pathString',
                'content'  => '/1/2/21/42/'
            ),
            $result,
            'Invalid or non-existing <Location> pathString value element.',
            false
        );
    }

    /**
     * Test if result contains depth value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDepthValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'depth',
                'content'  => '3'
            ),
            $result,
            'Invalid or non-existing <Location> depth value element.',
            false
        );
    }

    /**
     * Test if result contains sortField value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSortFieldValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'sortField',
                'content'  => 'PATH'
            ),
            $result,
            'Invalid or non-existing <Location> sortField value element.',
            false
        );
    }

    /**
     * Test if result contains sortOrder value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSortOrderValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'sortOrder',
                'content'  => 'ASC'
            ),
            $result,
            'Invalid or non-existing <Location> sortOrder value element.',
            false
        );
    }

    /**
     * Test if result contains childCount value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsChildCountValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'childCount',
                'content'  => '0'
            ),
            $result,
            'Invalid or non-existing <Location> childCount value element.',
            false
        );
    }

    /**
     * Get the Location visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestLocation
     */
    protected function getLocationVisitor()
    {
        return new ValueObjectVisitor\RestLocation(
            new Common\UrlHandler\eZPublish()
        );
    }
}
