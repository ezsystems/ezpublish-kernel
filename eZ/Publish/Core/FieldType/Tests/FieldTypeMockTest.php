<?php
/**
 * File containing the eZ\Publish\Core\FieldType\Tests\FieldTypeMockTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests;

use PHPUnit_Framework_TestCase;

class FieldTypeMockTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerForTestApplyDefaultSettings
     *
     * @covers \eZ\Publish\Core\FieldType\FieldType::applyDefaultSettings
     */
    public function testApplyDefaultSettings( $initialSettings, $expectedSettings )
    {
        $stub = $this->getMockForAbstractClass(
            "\\eZ\\Publish\\Core\\FieldType\\FieldType",
            array(),
            "",
            true,
            true,
            true,
            array( "getSettingsSchema" )
        );

        $stub
            ->expects( $this->any() )
            ->method( "getSettingsSchema" )
            ->will(
                $this->returnValue(
                    array(
                        "true" => array(
                            "default" => true
                        ),
                        "false" => array(
                            "default" => false
                        ),
                        "null" => array(
                            "default" => null
                        ),
                        "zero" => array(
                            "default" => 0
                        ),
                        "int" => array(
                            "default" => 42
                        ),
                        "float" => array(
                            "default" => 42.42
                        ),
                        "string" => array(
                            "default" => "string"
                        ),
                        "emptystring" => array(
                            "default" => ""
                        ),
                        "emptyarray" => array(
                            "default" => array()
                        ),
                        "nodefault" => array(),
                    )
                )
            );

        $fieldSettings = $initialSettings;
        $stub->applyDefaultSettings( $fieldSettings );
        $this->assertSame(
            $expectedSettings,
            $fieldSettings
        );
    }

    public function providerForTestApplyDefaultSettings()
    {
        return array(
            array(
                array(),
                array(
                    "true" => true,
                    "false" => false,
                    "null" => null,
                    "zero" => 0,
                    "int" => 42,
                    "float" => 42.42,
                    "string" => "string",
                    "emptystring" => "",
                    "emptyarray" => array(),
                )
            ),
            array(
                array(
                    "true" => "foo",
                ),
                array(
                    "true" => "foo",
                    "false" => false,
                    "null" => null,
                    "zero" => 0,
                    "int" => 42,
                    "float" => 42.42,
                    "string" => "string",
                    "emptystring" => "",
                    "emptyarray" => array(),
                )
            ),
            array(
                array(
                    "null" => "foo",
                ),
                array(
                    "null" => "foo",
                    "true" => true,
                    "false" => false,
                    "zero" => 0,
                    "int" => 42,
                    "float" => 42.42,
                    "string" => "string",
                    "emptystring" => "",
                    "emptyarray" => array(),
                )
            ),
            array(
                $array = array(
                    "false" => true,
                    "emptystring" => array( "foo" ),
                    "null" => "notNull",
                    "additionalEntry" => "baz",
                    "zero" => 10,
                    "int" => "this is not an int",
                    "string" => null,
                    "emptyarray" => array( array() ),
                    "true" => false,
                    "float" => true,
                ),
                $array
            ),
        );
    }
}
