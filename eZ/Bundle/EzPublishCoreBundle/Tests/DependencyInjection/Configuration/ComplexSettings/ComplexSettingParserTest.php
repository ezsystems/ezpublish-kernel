<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\ComplexSettings;

use PHPUnit_Framework_TestCase;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParser;

class ComplexSettingParserTest extends PHPUnit_Framework_TestCase
{
    /** @var ComplexSettingParser */
    private $parser;

    public function setUp()
    {
        $this->parser = new ComplexSettingParser();
    }

    /**
     * @dataProvider provideSettings
     */
    public function testContainsDynamicSettings( $setting, $expected )
    {
        self::assertEquals( $expected[1], $this->parser->containsDynamicSettings( $setting ), "string" );
    }

    /**
     * @dataProvider provideSettings
     */
    public function testParseComplexSetting( $setting, $expected )
    {
        self::assertEquals( $expected[2], $this->parser->parseComplexSetting( $setting ), "string" );
    }

    public function provideSettings()
    {
        // array( setting, array( isDynamicSetting, containsDynamicSettings ) )
        return array(
            array(
                '%container_var%',
                array( false, false, array() )
            ),
            array(
                '$somestring',
                array( false, false, array() )
            ),
            array(
                '$setting$',
                array( true, true, array( '$setting$' ) )
            ),
            array(

                '$setting;scope$',
                array( true, true, array( '$setting;scope$' ) )
            ),
            array(
                '$setting;namespace;scope$',
                array( true, true, array( '$setting;namespace;scope$' ) )
            ),
            array(
                'a_string_before$setting;namespace;scope$',
                array( false, true, array( '$setting;namespace;scope$' ) )
            ),
            array(
                '$setting;namespace;scope$a_string_after',
                array( false, true, array( '$setting;namespace;scope$' ) )
            ),
            array(
                'a_string_before$setting;namespace;scope$a_string_after',
                array( false, true, array( '$setting;namespace;scope$' ) )
            ),
            array(
                '$setting;one$somethingelse$setting;two$',
                array( false, true, array( '$setting;one$', '$setting;two$' ) )
            ),
        );
    }
}
