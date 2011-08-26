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
     * Test a new class and default values on properties
     * @covers \ezp\Content\Section::__construct
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
     * @covers \ezp\Content\Section::__construct
     */
    public function testMissingProperty()
    {
        $section = new Section();
        $value = $section->notDefined;
    }

    /**
     * @expectedException ezp\Base\Exception\PropertyPermission
     * @covers \ezp\Content\Section::__set
     */
    public function testReadOnlyProperty()
    {
        $section = new Section();
        $section->id = 22;
    }
}
