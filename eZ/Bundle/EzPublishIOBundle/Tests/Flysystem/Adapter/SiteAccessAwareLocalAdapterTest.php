<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Tests\Flysystem\Adapter;

use eZ\Bundle\EzPublishIOBundle\Flysystem\Adapter\SiteAccessAwareLocalAdapter;
use eZ\Publish\SPI\SiteAccess\ConfigProcessor;
use PHPUnit\Framework\TestCase;

class SiteAccessAwareLocalAdapterTest extends TestCase
{
    private const DYNAMIC_PATH = '__DYNAMIC_PATH__';
    private const STATIC_PATH = '__STATIC_PATH__';

    /** @var \eZ\Bundle\EzPublishCoreBundle\SiteAccess\Config\ComplexConfigProcessor|\PHPUnit\Framework\MockObject\MockObject */
    private $complexConfigProcessor;

    protected function setUp(): void
    {
        $this->complexConfigProcessor = $this->createMock(ConfigProcessor::class);
    }

    private static function getConfig(): array
    {
        return [
            'root' => self::getTemporaryRootDir(),
            'writeFlags' => LOCK_EX,
            'linkHandling' => SiteAccessAwareLocalAdapter::DISALLOW_LINKS,
            'permissions' => [],
            'path' => self::DYNAMIC_PATH,
        ];
    }

    public function testGetPathPrefix(): void
    {
        $this->complexConfigProcessor
            ->method('processSettingValue')
            ->with(self::equalTo(self::DYNAMIC_PATH))
            ->willReturn(self::STATIC_PATH);

        $adapter = new SiteAccessAwareLocalAdapter(
            $this->complexConfigProcessor,
            self::getConfig()
        );

        $expectedPath = self::getTemporaryRootDir() . '/' . self::STATIC_PATH;
        self::assertEquals($expectedPath, $adapter->getPathPrefix());
    }

    public static function tearDownAfterClass(): void
    {
        $dir = self::getTemporaryRootDir();
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }

    private static function getTemporaryRootDir(): string
    {
        return sys_get_temp_dir() . '/ezplatform-kernel-tests';
    }
}
