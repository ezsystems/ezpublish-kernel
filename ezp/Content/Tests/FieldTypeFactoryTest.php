<?php
/**
 * File containing the FieldTypeFactoryTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use PHPUnit_Framework_TestCase,
    ezp\Content\FieldType\Factory,
    ezp\Persistence\Content\FieldValue,
    ezp\Base\Configuration;

class FieldTypeFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Factory::getFieldTypeNamespace
     */
    public function testGetFieldTypeNSKnownType()
    {
        $fieldTypeMap = Configuration::getInstance( "content" )->get( "fields", "Type" );
        self::assertSame( $fieldTypeMap["ezstring"], Factory::getFieldTypeNamespace( "ezstring" ) );
    }

    /**
     * @expectedException \ezp\Base\Exception\MissingClass
     * @group fieldType
     * @covers \ezp\Content\FieldType\Factory::getFieldTypeNamespace
     */
    public function testGetFieldTypeNSUnknownType()
    {
        Factory::getFieldTypeNamespace( "eztestdoesnotexist" );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Factory::build
     */
    public function testBuild()
    {
        self::assertInstanceOf(
            "ezp\\Content\\FieldType",
            Factory::build( "ezstring" ),
            "Factory did not build a class of kind FieldType."
        );
    }

    /**
     * @group fieldType
     * @covers \ezp\Content\FieldType\Factory::buildValue
     */
    public function testBuildValueFromStringKnownType()
    {
        self::assertInstanceOf(
            "ezp\\Content\\FieldType\\Value",
            Factory::buildValue( "ezstring", "Working test" ),
            "Factory did not build a class of kind FieldType\\Value"
        );
    }
}
