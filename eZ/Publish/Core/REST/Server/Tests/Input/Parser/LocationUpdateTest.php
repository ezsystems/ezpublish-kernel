<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\Repository\LocationService;
use eZ\Publish\Core\REST\Server\Input\Parser\LocationUpdate;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\REST\Server\Values\RestLocationUpdateStruct;

class LocationUpdateTest extends BaseTest
{
    /**
     * Tests the LocationUpdate parser.
     */
    public function testParse()
    {
        $inputArray = [
            'priority' => 0,
            'remoteId' => 'remote-id',
            'hidden' => 'true',
            'sortField' => 'PATH',
            'sortOrder' => 'ASC',
        ];

        $locationUpdate = $this->getParser();
        $result = $locationUpdate->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertInstanceOf(
            RestLocationUpdateStruct::class,
            $result,
            'LocationUpdateStruct not created correctly.'
        );

        $this->assertInstanceOf(
            LocationUpdateStruct::class,
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

        $this->assertTrue(
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
     * Test LocationUpdate parser throwing exception on missing sort field.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'sortField' element for LocationUpdate.
     */
    public function testParseExceptionOnMissingSortField()
    {
        $inputArray = [
            'priority' => 0,
            'remoteId' => 'remote-id',
            'sortOrder' => 'ASC',
        ];

        $locationUpdate = $this->getParser();
        $locationUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test LocationUpdate parser throwing exception on missing sort order.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'sortOrder' element for LocationUpdate.
     */
    public function testParseExceptionOnMissingSortOrder()
    {
        $inputArray = [
            'priority' => 0,
            'remoteId' => 'remote-id',
            'sortField' => 'PATH',
        ];

        $locationUpdate = $this->getParser();
        $locationUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the LocationUpdateStruct parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\LocationUpdate
     */
    protected function internalGetParser()
    {
        return new LocationUpdate(
            $this->getLocationServiceMock(),
            $this->getParserTools()
        );
    }

    /**
     * Get the location service mock object.
     *
     * @return \eZ\Publish\API\Repository\LocationService
     */
    protected function getLocationServiceMock()
    {
        $locationServiceMock = $this->createMock(LocationService::class);

        $locationServiceMock->expects($this->any())
            ->method('newLocationUpdateStruct')
            ->will(
                $this->returnValue(new LocationUpdateStruct())
            );

        return $locationServiceMock;
    }
}
