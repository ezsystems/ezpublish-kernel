<?php
/**
 * File contains: ezp\Publish\PublicAPI\Tests\SectionTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Publish\PublicAPI\Tests;
use ezp\PublicAPI\Values\Content\Section;

/**
 * Test case for Section class
 *
 */
class SectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test a new class and default values on properties
     * @covers \ezp\PublicAPI\Values\Content\Section::__construct
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
     * @covers \ezp\PublicAPI\Values\Content\Section::__get
     */
    public function testMissingProperty()
    {
        $section = new Section();
        $value = $section->notDefined;
    }

    /**
     * @expectedException ezp\Base\Exception\PropertyPermission
     * @covers \ezp\PublicAPI\Values\Content\Section::__set
     */
    public function testReadOnlyProperty()
    {
    	self::markTestSkipped( 'ID is a public property, will not fail' );
        $section = new Section();
        $section->id = 22;
    }
}
