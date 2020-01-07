<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\SiteAccess\Config;

use eZ\Bundle\EzPublishCoreBundle\SiteAccess\Config\ComplexConfigProcessor;
use eZ\Bundle\EzPublishCoreBundle\SiteAccess\Config\IOConfigResolver;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessService;
use PHPUnit\Framework\TestCase;

class IOConfigResolverTest extends TestCase
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessService|\PHPUnit\Framework\MockObject\MockObject */
    private $siteAccessService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->siteAccessService = $this->createMock(SiteAccessService::class);
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\Config\IOConfigResolver::getUrlPrefix
     */
    public function testGetUrlPrefix(): void
    {
        $this->siteAccessService
            ->method('getCurrent')
            ->willReturn(new SiteAccess('ezdemo_site'));

        $this->configResolver
            ->method('hasParameter')
            ->with('io.url_prefix', null, 'ezdemo_site')
            ->willReturn(true);
        $this->configResolver
            ->method('getParameter')
            ->willReturnMap([
                ['io.url_prefix', null, 'ezdemo_site', '$var_dir$/ezdemo_site/$storage_dir$'],
                ['var_dir', 'ezsettings', 'ezdemo_site', 'var'],
                ['storage_dir', 'ezsettings', 'ezdemo_site', 'storage'],
            ]);

        $complexConfigProcessor = new ComplexConfigProcessor(
            $this->configResolver,
            $this->siteAccessService
        );

        $ioConfigResolver = new IOConfigResolver(
            $complexConfigProcessor
        );

        $this->assertEquals('var/ezdemo_site/storage', $ioConfigResolver->getUrlPrefix());
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\Config\IOConfigResolver::getUrlPrefix
     */
    public function testGetLegacyUrlPrefix(): void
    {
        $this->siteAccessService
            ->method('getCurrent')
            ->willReturn(new SiteAccess('ezdemo_site'));

        $this->configResolver
            ->method('hasParameter')
            ->with('io.legacy_url_prefix', null, 'ezdemo_site')
            ->willReturn(true);
        $this->configResolver
            ->method('getParameter')
            ->willReturnMap([
                ['io.legacy_url_prefix', null, 'ezdemo_site', '$var_dir$/ezdemo_site/$storage_dir$'],
                ['var_dir', 'ezsettings', 'ezdemo_site', 'var'],
                ['storage_dir', 'ezsettings', 'ezdemo_site', 'legacy_storage'],
            ]);

        $complexConfigProcessor = new ComplexConfigProcessor(
            $this->configResolver,
            $this->siteAccessService
        );

        $ioConfigResolver = new IOConfigResolver(
            $complexConfigProcessor
        );

        $this->assertEquals('var/ezdemo_site/legacy_storage', $ioConfigResolver->getLegacyUrlPrefix());
    }

    /**
     * @covers \eZ\Bundle\EzPublishCoreBundle\SiteAccess\Config\IOConfigResolver::getUrlPrefix
     */
    public function testGetRootDir(): void
    {
        $this->siteAccessService
            ->method('getCurrent')
            ->willReturn(new SiteAccess('ezdemo_site'));

        $this->configResolver
            ->method('hasParameter')
            ->with('io.root_dir', null, 'ezdemo_site')
            ->willReturn(true);
        $this->configResolver
            ->method('getParameter')
            ->willReturnMap([
                ['io.root_dir', null, 'ezdemo_site', '/path/to/ezpublish/web/$var_dir$/ezdemo_site/$storage_dir$'],
                ['var_dir', 'ezsettings', 'ezdemo_site', 'var'],
                ['storage_dir', 'ezsettings', 'ezdemo_site', 'legacy_storage'],
            ]);

        $complexConfigProcessor = new ComplexConfigProcessor(
            $this->configResolver,
            $this->siteAccessService
        );

        $ioConfigResolver = new IOConfigResolver(
            $complexConfigProcessor
        );

        $this->assertEquals('/path/to/ezpublish/web/var/ezdemo_site/legacy_storage', $ioConfigResolver->getRootDir());
    }
}
