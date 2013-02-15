<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\LocationUpdate;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Location;

class LocationUpdateTest extends BaseTest
{
    /**
     * Tests the LocationUpdate parser
     */
    public function testParse()
    {
        $inputArray = array(
            'priority' => 0,
            'remoteId' => 'remote-id',
            'hidden' => 'true',
            'sortField' => 'PATH',
            'sortOrder' => 'ASC'
        );

        $locationUpdate = $this->getLocationUpdate();
        $result = $locationUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\Core\\REST\\Server\\Values\\RestLocationUpdateStruct',
            $result,
            'LocationUpdateStruct not created correctly.'
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationUpdateStruct',
            $result->locationUpdateStruct,
            'LocationUpdateStruct not created correctly.'
        );

        $this->assertEquals(
            0,
            $result->locationUpdateStruct->priority,
            'LocationUpdateStruct priority property not created correctly.'
        );

        $this->assertEquals(
            'remote-id',
            $result->locationUpdateStruct->remoteId,
            'LocationUpdateStruct remoteId property not created correctly.'
        );

        $this->assertEquals(
            true,
            $result->hidden,
            'hidden property not created correctly.'
        );

        $this->assertEquals(
            Location::SORT_FIELD_PATH,
            $result->locationUpdateStruct->sortField,
            'LocationUpdateStruct sortField property not created correctly.'
        );

        $this->assertEquals(
            Location::SORT_ORDER_ASC,
            $result->locationUpdateStruct->sortOrder,
            'LocationUpdateStruct sortOrder property not created correctly.'
        );
    }

    /**
     * Test LocationUpdate parser throwing exception on missing sort field
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'sortField' element for LocationUpdate.
     */
    public function testParseExceptionOnMissingSortField()
    {
        $inputArray = array(
            'priority' => 0,
            'remoteId' => 'remote-id',
            'sortOrder' => 'ASC'
        );

        $locationUpdate = $this->getLocationUpdate();
        $locationUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test LocationUpdate parser throwing exception on missing sort order
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'sortOrder' element for LocationUpdate.
     */
    public function testParseExceptionOnMissingSortOrder()
    {
        $inputArray = array(
            'priority' => 0,
            'remoteId' => 'remote-id',
            'sortField' => 'PATH'
        );

        $locationUpdate = $this->getLocationUpdate();
        $locationUpdate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the LocationUpdateStruct parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\LocationUpdate
     */
    protected function getLocationUpdate()
    {
        return new LocationUpdate(
            $this->getUrlHandler(),
            $this->getLocationServiceMock(),
            $this->getParserTools()
        );
    }

    /**
     * Get the location service mock object
     *
     * @return \eZ\Publish\API\Repository\LocationService
     */
    protected function getLocationServiceMock()
    {
        $locationServiceMock = $this->getMock(
            'eZ\\Publish\\Core\\Repository\\LocationService',
            array(),
            array(),
            '',
            false
        );

        $locationServiceMock->expects( $this->any() )
            ->method( 'newLocationUpdateStruct' )
            ->will(
                $this->returnValue( new LocationUpdateStruct() )
            );

        return $locationServiceMock;
    }
}
