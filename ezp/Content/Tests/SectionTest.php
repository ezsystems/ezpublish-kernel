<?php
/**
 * File contains: ezp\Content\Tests\LocationTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content\Section,
    ezp\Base\Service\Container;

/**
 * Test case for Location class
 *
 */
class SectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \ezp\Content\Section\Service
     */
    protected function getService()
    {
        $serviceContainer = new Container(
            array(
                '@repository_handler' => new \ezp\Persistence\Tests\InMemoryEngine\RepositoryHandler()
            )
        );
        return $serviceContainer->getRepository()->getSectionService();
    }

    /**
     * Test a new class and default values on properties
     */
    public function testNewClass()
    {
        $section = new Section();
        self::assertEquals( $section->id, null );
        self::assertEquals( $section->identifier, null );
        self::assertEquals( $section->name, null );
    }

    /**
     * @expectedException ezp\Base\Exception\PropertyNotFound
     */
    public function testMissingProperty()
    {
        $section = new Section();
        $value = $section->notDefined;
    }

    /**
     * @expectedException ezp\Base\Exception\PropertyPermission
     */
    public function testReadOnlyProperty()
    {
        $section = new Section();
        $section->id = 22;
    }

    /**
     * Test service function for creating sections
     */
    public function testCreate()
    {
        $section = new Section();
        $section->identifier = 'test';
        $section->name = 'Test';

        $service = $this->getService();
        $newSection = $service->create( $section );
        //self::assertEquals( $newSection->id, 2 );
        self::assertEquals( $newSection->identifier, $section->identifier );
        self::assertEquals( $newSection->name, $section->name );
    }

    /**
     * Test service function for deleting sections
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDelete()
    {
        $section = new Section();
        $section->identifier = 'test';
        $section->name = 'Test';

        $service = $this->getService();
        $section = $service->create( $section );    
        $service->delete( $section->id );
        $service->load( $section->id );
    }

    /**
     * Test service function for loading sections
     */
    public function testLoad()
    {
        $section = new Section();
        $section->identifier = 'test';
        $section->name = 'Test';

        $service = $this->getService();
        $section = $service->create( $section );
        $newSection = $service->load( $section->id );
        //self::assertEquals( $newSection->id, 2 );
        self::assertEquals( $newSection->identifier, $section->identifier );
        self::assertEquals( $newSection->name, $section->name );

    }

     /**
     * Test service function for loading sections
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLoadNotFound()
    {
        $service = $this->getService();
        $service->load( 42 );
    }
}
