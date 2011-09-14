<?php
/**
 * File containing the FieldTypeFactoryTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use PHPUnit_Framework_TestCase,
    ezp\Content\FieldType\Factory;

class FieldTypeFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException ezp\Base\Exception\MissingClass
     * @covers ezp\Content\FieldType\Factory::build
     */
    public function testBuildUnknownType()
    {
        Factory::build( "eztestdoesnotexist" );
    }

    /**
     * @covers ezp\Content\FieldType\Factory::build
     */
    public function testBuildKnownType()
    {
        self::assertInstanceOf(
            "ezp\\Content\\FieldType",
            Factory::build( "ezstring" ),
            "Factory did not build a class of kind FieldType."
        );
    }
}
