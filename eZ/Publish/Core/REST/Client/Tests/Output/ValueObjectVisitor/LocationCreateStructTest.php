<?php
/**
 * File containing a LocationCreateStructTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\REST\Common;

class LocationCreateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the LocationCreateStruct visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getLocationCreateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $locationCreateStruct = new LocationCreateStruct();
        $locationCreateStruct->hidden = false;
        $locationCreateStruct->parentLocationId = '/content/locations/1/2/42';
        $locationCreateStruct->priority = 0;
        $locationCreateStruct->remoteId = 'remote-id';
        $locationCreateStruct->sortField = Location::SORT_FIELD_PATH;
        $locationCreateStruct->sortOrder = Location::SORT_ORDER_ASC;

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $locationCreateStruct
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the result contains LocationCreate element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationCreateElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'LocationCreate',
                'children' => array(
                    'count' => 5
                )
            ),
            $result,
            'Invalid <LocationCreate> element.',
            false
        );
    }

    /**
     * Tests that the result contains LocationCreate attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationCreateAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'LocationCreate',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.LocationCreate+xml',
                )
            ),
            $result,
            'Invalid <LocationCreate> attributes.',
            false
        );
    }

    /**
     * Tests that the result contains ParentLocation element
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
     * Tests that the result contains ParentLocation attributes
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
                    'href' => '/content/locations/1/2/42'
                )
            ),
            $result,
            'Invalid <ParentLocation> attributes.',
            false
        );
    }

    /**
     * Tests that the result contains priority value element
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
                'content'  => '0',
            ),
            $result,
            'Invalid or non-existing <LocationCreate> priority value element.',
            false
        );
    }

    /**
     * Tests that the result contains hidden value element
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
                'content'  => 'false',
            ),
            $result,
            'Invalid or non-existing <LocationCreate> hidden value element.',
            false
        );
    }

    /**
     * Tests that the result contains sortField value element
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
                'content'  => 'PATH',
            ),
            $result,
            'Invalid or non-existing <LocationCreate> sortField value element.',
            false
        );
    }

    /**
     * Tests that the result contains sortOrder value element
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
                'content'  => 'ASC',
            ),
            $result,
            'Invalid or non-existing <LocationCreate> sortOrder value element.',
            false
        );
    }

    /**
     * Gets the LocationCreateStruct visitor
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\LocationCreateStruct
     */
    protected function getLocationCreateStructVisitor()
    {
        return new ValueObjectVisitor\LocationCreateStruct(
            new Common\UrlHandler\eZPublish()
        );
    }
}
