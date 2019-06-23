<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Publish\Core\IO\Tests\UrlDecorator;

use eZ\Publish\Core\IO\UrlDecorator\Prefix;
use PHPUnit\Framework\TestCase;

class PrefixTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testDecorate($url, $prefix, $decoratedUrl)
    {
        $decorator = $this->buildDecorator($prefix);

        self::assertEquals(
            $decoratedUrl,
            $decorator->decorate($url)
        );
    }

    /**
     * @dataProvider provideData
     */
    public function testUndecorate($url, $prefix, $decoratedUrl)
    {
        $decorator = $this->buildDecorator($prefix);

        self::assertEquals(
            $url,
            $decorator->undecorate($decoratedUrl)
        );
    }

    /**
     * @param $prefix
     *
     * @return \eZ\Publish\Core\IO\UrlDecorator
     */
    protected function buildDecorator($prefix)
    {
        return new Prefix($prefix);
    }

    public function provideData()
    {
        return [
            [
                'images/file.png',
                'var/storage',
                'var/storage/images/file.png',
            ],
            [
                'images/file.png',
                'var/storage/',
                'var/storage/images/file.png',
            ],
            [
                'images/file.png',
                'http://static.example.com',
                'http://static.example.com/images/file.png',
            ],
        ];
    }
}
