<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\IO\Tests\UrlDecorator;

use eZ\Publish\Core\IO\IOConfig;
use eZ\Publish\Core\IO\UrlDecorator\AbsolutePrefix;

/**
 * Test case for IO Service.
 */
class AbsolutePrefixTest extends PrefixTest
{
    protected function buildDecorator($prefix)
    {
        $ioConfigResolverMock = $this->createMock(IOConfig::class);
        $ioConfigResolverMock
            ->method('getLegacyUrlPrefix')
            ->willReturn($prefix);

        return new AbsolutePrefix($ioConfigResolverMock);
    }

    public function provideData()
    {
        return [
            [
                'images/file.png',
                'var/storage',
                '/var/storage/images/file.png',
            ],
            [
                'images/file.png',
                'var/storage/',
                '/var/storage/images/file.png',
            ],
            [
                'images/file.png',
                'http://static.example.com',
                'http://static.example.com/images/file.png',
            ],
            [
                'images/file.png',
                'http://static.example.com/',
                'http://static.example.com/images/file.png',
            ],
            [
                'images/file.png',
                '//static.example.com',
                '//static.example.com/images/file.png',
            ],
            [
                'images/file.png',
                '//static.example.com/',
                '//static.example.com/images/file.png',
            ],
        ];
    }
}
