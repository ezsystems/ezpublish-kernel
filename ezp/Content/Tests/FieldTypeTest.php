<?php
/**
 * File containing the FieldTypeTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use PHPUnit_Framework_TestCase,
    ezp\Content\FieldType;

class FieldTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException ezp\Base\Exception\MissingClass
     * @covers ezp\Content\FieldType::create
     */
    public function testCreateUnknownType()
    {
        FieldType::create( 'eztestdoesnotexist' );
    }

    /**
     * @covers ezp\Content\FieldType::create
     */
    public function testCreateKnownType()
    {
        $ft = FieldType::create( 'ezstring' );
        self::assertInstanceOf( 'ezp\\Content\\FieldType', $ft, "Factory did not create a class of kind FieldType." );
    }
}
