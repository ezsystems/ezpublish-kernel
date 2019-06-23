<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\MapperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Location;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper;
use eZ\Publish\SPI\Persistence\Content\Location\Trashed;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;

/**
 * Test case for Location\Mapper.
 */
class MapperTest extends TestCase
{
    /**
     * Location data from the database.
     *
     * @var array
     */
    protected $locationRow = [
        'node_id' => 77,
        'priority' => 0,
        'is_hidden' => 0,
        'is_invisible' => 0,
        'remote_id' => 'dbc2f3c8716c12f32c379dbf0b1cb133',
        'contentobject_id' => 75,
        'contentobject_version' => 1,
        'parent_node_id' => 2,
        'path_identification_string' => 'solutions',
        'path_string' => '/1/2/77/',
        'modified_subnode' => 1311065017,
        'main_node_id' => 77,
        'depth' => 2,
        'sort_field' => 2,
        'sort_order' => 1,
    ];

    /**
     * Expected Location object properties values.
     *
     * @var array
     */
    protected $locationValues = [
        'id' => 77,
        'priority' => 0,
        'hidden' => false,
        'invisible' => false,
        'remoteId' => 'dbc2f3c8716c12f32c379dbf0b1cb133',
        'contentId' => 75,
        'parentId' => 2,
        'pathIdentificationString' => 'solutions',
        'pathString' => '/1/2/77/',
        'depth' => 2,
        'sortField' => 2,
        'sortOrder' => 1,
    ];

    /**
     * Expected Location CreateStruct object properties values.
     *
     * @var array
     */
    protected $locationCreateStructValues = [
        'contentId' => 75,
        'contentVersion' => 1,
        'hidden' => 0,
        'invisible' => 0,
        'parentId' => 2,
        'pathIdentificationString' => 'solutions',
        'priority' => 0,
        'sortField' => 2,
        'sortOrder' => 1,
    ];

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper::createLocationFromRow
     */
    public function testCreateLocationFromRow()
    {
        $mapper = new Mapper();

        $location = $mapper->createLocationFromRow(
            $this->locationRow
        );

        $this->assertPropertiesCorrect(
            $this->locationValues,
            $location
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper::createLocationsFromRows
     */
    public function testCreateLocationsFromRows()
    {
        $inputRows = [];
        for ($i = 0; $i < 3; ++$i) {
            $row = $this->locationRow;
            $row['node_id'] += $i;
            $inputRows[] = $row;
        }

        $mapper = new Mapper();

        $locations = $mapper->createLocationsFromRows($inputRows);

        $this->assertCount(3, $locations);
        foreach ($locations as $location) {
            $this->assertInstanceOf(
                SPILocation::class,
                $location
            );
        }
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper::createLocationFromRow
     */
    public function testCreateTrashedFromRow()
    {
        $mapper = new Mapper();

        $location = $mapper->createLocationFromRow(
            $this->locationRow,
            null,
            new Trashed()
        );

        $this->assertTrue($location instanceof Trashed);
        $this->assertPropertiesCorrect(
            $this->locationValues,
            $location
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper::createLocationFromRow
     */
    public function testCreateLocationFromRowWithPrefix()
    {
        $prefix = 'some_prefix_';

        $data = [];
        foreach ($this->locationRow as $key => $val) {
            $data[$prefix . $key] = $val;
        }

        $mapper = new Mapper();

        $location = $mapper->createLocationFromRow($data, $prefix);

        $this->assertPropertiesCorrect(
            $this->locationValues,
            $location
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper::getLocationCreateStruct
     */
    public function testGetLocationCreateStruct()
    {
        $mapper = new Mapper();

        $createStruct = $mapper->getLocationCreateStruct(
            $this->locationRow
        );

        $this->assertNotEquals($this->locationRow['remote_id'], $createStruct->remoteId);
        $this->assertPropertiesCorrect(
            $this->locationCreateStructValues,
            $createStruct
        );
    }
}
