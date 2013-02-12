<?php
/**
 * File containing a LocationUpdateStructTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\REST\Common;

class LocationUpdateStructTest extends ValueObjectVisitorBaseTest
{
    /**
     * Tests the LocationUpdateStruct visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getLocationUpdateStructVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $locationUpdateStruct = new LocationUpdateStruct();
        $locationUpdateStruct->priority = 0;
        $locationUpdateStruct->remoteId = 'remote-id';
        $locationUpdateStruct->sortField = Location::SORT_FIELD_PATH;
        $locationUpdateStruct->sortOrder = Location::SORT_ORDER_ASC;

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $locationUpdateStruct
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Tests that the result contains LocationUpdate element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationUpdateElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'LocationUpdate',
                'children' => array(
                    'count' => 4
                )
            ),
            $result,
            'Invalid <LocationUpdate> element.',
            false
        );
    }

    /**
     * Tests that the result contains LocationUpdate attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsLocationUpdateAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'LocationUpdate',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.LocationUpdate+xml',
                )
            ),
            $result,
            'Invalid <LocationUpdate> attributes.',
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
            'Invalid or non-existing <LocationUpdate> priority value element.',
            false
        );
    }

    /**
     * Tests that the result contains remoteId value element
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
                'content'  => 'remote-id',
            ),
            $result,
            'Invalid or non-existing <LocationUpdate> hidden value element.',
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
            'Invalid or non-existing <LocationUpdate> sortField value element.',
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
            'Invalid or non-existing <LocationUpdate> sortOrder value element.',
            false
        );
    }

    /**
     * Gets the LocationUpdateStruct visitor
     *
     * @return \eZ\Publish\Core\REST\Client\Output\ValueObjectVisitor\LocationUpdateStruct
     */
    protected function getLocationUpdateStructVisitor()
    {
        return new ValueObjectVisitor\LocationUpdateStruct(
            new Common\UrlHandler\eZPublish()
        );
    }
}
