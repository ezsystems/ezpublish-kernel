<?php

/**
 * File containing the DynamicSettingParserTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\SiteAccessAware;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParser;
use PHPUnit_Framework_TestCase;

class DynamicSettingParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider isDynamicSettingProvider
     */
    public function testIsDynamicSetting($setting, $expected)
    {
        $parser = new DynamicSettingParser();
        $this->assertSame($expected, $parser->isDynamicSetting($setting));
    }

    public function isDynamicSettingProvider()
    {
        return array(
            array('foo', false),
            array('%foo%', false),
            array('$foo', false),
            array('foo$', false),
            array('$foo$', true),
            array('$foo.bar$', true),
            array('$foo_bar$', true),
            array('$foo.bar$', true),
            array('$foo;ba_bar$', true),
            array('$foo;babar.elephant$', true),
            array('$foo;babar;elephant$', true),
            array('$foo;bar;baz_biz$', true),
            array('$foo$/$bar$', false),
        );
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testParseDynamicSettingFail()
    {
        $parser = new DynamicSettingParser();
        $parser->parseDynamicSetting('$foo;bar;baz;biz$');
    }

    /**
     * @dataProvider parseDynamicSettingProvider
     */
    public function testParseDynamicSetting($setting, array $expected)
    {
        $parser = new DynamicSettingParser();
        $this->assertSame($expected, $parser->parseDynamicSetting($setting));
    }

    public function parseDynamicSettingProvider()
    {
        return array(
            array(
                '$foo$',
                array(
                    'param' => 'foo',
                    'namespace' => null,
                    'scope' => null,
                ),
            ),
            array(
                '$foo.bar$',
                array(
                    'param' => 'foo.bar',
                    'namespace' => null,
                    'scope' => null,
                ),
            ),
            array(
                '$foo;bar$',
                array(
                    'param' => 'foo',
                    'namespace' => 'bar',
                    'scope' => null,
                ),
            ),
            array(
                '$foo;ba_bar;biz$',
                array(
                    'param' => 'foo',
                    'namespace' => 'ba_bar',
                    'scope' => 'biz',
                ),
            ),
        );
    }
}
