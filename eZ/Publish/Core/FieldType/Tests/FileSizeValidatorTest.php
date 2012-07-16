<?php
/**
 * File containing the FileSizeValidatorTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;
use eZ\Publish\Core\FieldType\BinaryFile\Value as BinaryFileValue,
    eZ\Publish\Core\FieldType\Validator\FileSizeValidator,
    eZ\Publish\Core\Repository\Tests\FieldType;

/**
 * @group fieldType
 * @group validator
 */
class FileSizeValidatorTest extends FieldTypeTest
{
    /**
     * This test ensure an FileSizeValidator can be created
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(
            "eZ\\Publish\\Core\\FieldType\\Validator",
            new FileSizeValidator
        );
    }

    // @TODO test the rest
}
