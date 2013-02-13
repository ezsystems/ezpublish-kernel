<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Server\Input\Parser\LocationCreate;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Location;

class LocationCreateTest extends BaseTest
{
    /**
     * Tests the LocationCreate parser
     */
    public function testParse()
    {
        $inputArray = array(
            'ParentLocation' => array(
                '_href' => '/content/locations/1/2/42'
            ),
            'priority' => '2',
            'hidden' => 'true',
            'remoteId' => 'remoteId12345678',
            'sortField' => 'PATH',
            'sortOrder' => 'ASC'
        );

        $locationCreate = $this->getLocationCreate();
        $result = $locationCreate->parse( $inputArray, $this->getParsingDispatcherMock() );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationCreateStruct',
            $result,
            'LocationCreateStruct not created correctly.'
        );

        $this->assertEquals(
            42,
            $result->parentLocationId,
            'LocationCreateStruct parentLocationId property not created correctly.'
        );

        $this->assertEquals(
            2,
            $result->priority,
            'LocationCreateStruct priority property not created correctly.'
        );

        $this->assertEquals(
            true,
            $result->hidden,
            'LocationCreateStruct hidden property not created correctly.'
        );

        $this->assertEquals(
            'remoteId12345678',
            $result->remoteId,
            'LocationCreateStruct remoteId property not created correctly.'
        );

        $this->assertEquals(
            Location::SORT_FIELD_PATH,
            $result->sortField,
            'LocationCreateStruct sortField property not created correctly.'
        );

        $this->assertEquals(
            Location::SORT_ORDER_ASC,
            $result->sortOrder,
            'LocationCreateStruct sortOrder property not created correctly.'
        );
    }

    /**
     * Test LocationCreate parser throwing exception on missing ParentLocation
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing or invalid 'ParentLocation' element for LocationCreate.
     */
    public function testParseExceptionOnMissingParentLocation()
    {
        $inputArray = array(
            'priority' => '0',
            'hidden' => 'false',
            'remoteId' => 'remoteId12345678',
            'sortField' => 'PATH',
            'sortOrder' => 'ASC'
        );

        $locationCreate = $this->getLocationCreate();
        $locationCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test LocationCreate parser throwing exception on missing _href attribute for ParentLocation
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing '_href' attribute for ParentLocation element in LocationCreate.
     */
    public function testParseExceptionOnMissingHrefAttribute()
    {
        $inputArray = array(
            'ParentLocation' => array(),
            'priority' => '0',
            'hidden' => 'false',
            'remoteId' => 'remoteId12345678',
            'sortField' => 'PATH',
            'sortOrder' => 'ASC'
        );

        $locationCreate = $this->getLocationCreate();
        $locationCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test LocationCreate parser throwing exception on missing sort field
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'sortField' element for LocationCreate.
     */
    public function testParseExceptionOnMissingSortField()
    {
        $inputArray = array(
            'ParentLocation' => array(
                '_href' => '/content/locations/1/2/42'
            ),
            'priority' => '0',
            'hidden' => 'false',
            'remoteId' => 'remoteId12345678',
            'sortOrder' => 'ASC'
        );

        $locationCreate = $this->getLocationCreate();
        $locationCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Test LocationCreate parser throwing exception on missing sort order
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'sortOrder' element for LocationCreate.
     */
    public function testParseExceptionOnMissingSortOrder()
    {
        $inputArray = array(
            'ParentLocation' => array(
                '_href' => '/content/locations/1/2/42'
            ),
            'priority' => '0',
            'hidden' => 'false',
            'remoteId' => 'remoteId12345678',
            'sortField' => 'PATH'
        );

        $locationCreate = $this->getLocationCreate();
        $locationCreate->parse( $inputArray, $this->getParsingDispatcherMock() );
    }

    /**
     * Returns the LocationCreateStruct parser
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\LocationCreate
     */
    protected function getLocationCreate()
    {
        return new LocationCreate(
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
            ->method( 'newLocationCreateStruct' )
            ->with( $this->equalTo( 42 ) )
            ->will(
                $this->returnValue( new LocationCreateStruct( array( 'parentLocationId' => 42 ) ) )
            );

        return $locationServiceMock;
    }
}
