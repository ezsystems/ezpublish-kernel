<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\Content\Type\CreateStructTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests\Content\Type;

use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;

/**
 * Test case for CreateStruct.
 */
class CreateStructTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testClone()
    {
        $cStruct = new CreateStruct();

        $cStruct->identifier = "foo";
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
}
