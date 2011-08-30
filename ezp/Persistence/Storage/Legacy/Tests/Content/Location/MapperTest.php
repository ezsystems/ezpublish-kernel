<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\LocationHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Location;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content\Location\Mapper;

/**
 * Test case for Location\Mapper
 */
class LocationHandlerTest extends TestCase
{
    /**
     * Location data from the database
     *
     * @var string[]
     */
    protected $locationData = array(
        'node_id' => 77,
        'priority' => 0,
        'is_hidden' => 0,
        'is_invisible' => 0,
        'remote_id' => 'dbc2f3c8716c12f32c379dbf0b1cb133',
        'contentobject_id' => 75,
        'parent_node_id' => 2,
        'path_identification_string' => 'solutions',
        'path_string' => '/1/2/77/',
        'modified_subnode' => 1311065017,
        'main_node_id' => 77,
        'depth' => 2,
        'sort_field' => 2,
        'sort_order' => 1,
    );

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    /**
     * Data provider for testCreateLocation(  )
     *
     * @return void
     */
    public static function getLoadLocationValues()
    {
        return array(
            array( 'id', 77 ),
            array( 'priority', 0 ),
            array( 'hidden', 0 ),
            array( 'invisible', 0 ),
            array( 'remoteId', 'dbc2f3c8716c12f32c379dbf0b1cb133' ),
            array( 'contentId', 75 ),
            array( 'parentId', 2 ),
            array( 'pathIdentificationString', 'solutions' ),
            array( 'pathString', '/1/2/77/' ),
            array( 'modifiedSubLocation', 1311065017 ),
            array( 'mainLocationId', 77 ),
            array( 'depth', 2 ),
            array( 'sortField', 2 ),
            array( 'sortOrder', 1 ),
        );
    }

    /**
     * @return void
     * @dataProvider getLoadLocationValues
     * @covers ezp\Persistence\Storage\Legacy\Content\Location\Mapper->createLocationFromRow
     */
    public function testCreateLocationFromRow( $field, $value )
    {
        $mapper = new Mapper();

        $location = $mapper->createLocationFromRow(
            $this->locationData
        );

        $this->assertEquals(
            $value,
            $location->$field,
            "Property \\$$field not set correctly"
        );
    }

    /**
     * @return void
     * @dataProvider getLoadLocationValues
     * @covers ezp\Persistence\Storage\Legacy\Content\Location\Mapper->createLocationFromRow
     */
    public function testCreateLocationFromRowWithPrefix( $field, $value )
    {
        $prefix = 'some_prefix_';

        $data = array();
        foreach ( $this->locationData as $key => $val )
        {
            $data[$prefix . $key] = $val;
        }

        $mapper = new Mapper();

        $location = $mapper->createLocationFromRow(
            $data
        );

        $this->assertEquals(
            $value,
            $location->$field,
            "Property \\$$field not set correctly"
        );
    }
}
