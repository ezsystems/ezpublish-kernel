<?php

/**
 * File containing the DynamicSettingParserTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\SiteAccessAware;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParser;
use PHPUnit\Framework\TestCase;

class DynamicSettingParserTest extends TestCase
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
        return [
            ['foo', false],
            ['%foo%', false],
            ['$foo', false],
            ['foo$', false],
            ['$foo$', true],
            ['$foo.bar$', true],
            ['$foo_bar$', true],
            ['$foo.bar$', true],
            ['$foo;ba_bar$', true],
            ['$foo;babar.elephant$', true],
            ['$foo;babar;elephant$', true],
            ['$foo;bar;baz_biz$', true],
            ['$foo$/$bar$', false],
        ];
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
        return [
            [
                '$foo$',
                [
                    'param' => 'foo',
                    'namespace' => null,
                    'scope' => null,
                ],
            ],
            [
                '$foo.bar$',
                [
                    'param' => 'foo.bar',
                    'namespace' => null,
                    'scope' => null,
                ],
            ],
            [
                '$foo;bar$',
                [
                    'param' => 'foo',
                    'namespace' => 'bar',
                    'scope' => null,
                ],
            ],
            [
                '$foo;ba_bar;biz$',
                [
                    'param' => 'foo',
                    'namespace' => 'ba_bar',
                    'scope' => 'biz',
                ],
            ],
        ];
    }
}
