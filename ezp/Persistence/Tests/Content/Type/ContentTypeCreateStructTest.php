<?php
/**
 * File contains: ezp\Persistence\Tests\Content\Type\ContentTypeCreateStructTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\Content\Type;
use ezp\Persistence\Content\Type\ContentTypeCreateStruct,
    ezp\Persistence\Content\Type\FieldDefinition;

/**
 * Test case for ContentTypeCreateStruct.
 *
 */
class ContentTypeCreateStructTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testClone()
    {
        $cStruct = new ContentTypeCreateStruct();

        $cStruct->identifier       = "foo";
        $cStruct->fieldDefinitions = array(
            new FieldDefinition(),
        );

        $cStructClone = clone $cStruct;

        $this->assertEquals(
            $cStruct->identifier,
            $cStructClone->identifier,
            "Identifier not cloned correctly"
        );
        $this->assertNotSame(
            $cStruct->fieldDefinitions[0],
            $cStructClone->fieldDefinitions[0],
            "Field definitions not cloned"
        );
        $this->assertEquals(
            $cStruct->fieldDefinitions[0],
            $cStructClone->fieldDefinitions[0],
            "Field definitions not clonedc correctly"
        );
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
