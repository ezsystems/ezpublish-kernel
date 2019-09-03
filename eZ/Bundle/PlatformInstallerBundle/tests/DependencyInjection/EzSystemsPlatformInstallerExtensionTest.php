<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\PlatformInstallerBundleTests\DependencyInjection;

use EzSystems\PlatformInstallerBundle\Command\InstallPlatformCommand;
use EzSystems\PlatformInstallerBundle\DependencyInjection\Compiler\InstallerTagPass;
use EzSystems\PlatformInstallerBundle\DependencyInjection\EzSystemsPlatformInstallerExtension;
use EzSystems\PlatformInstallerBundle\Installer\CoreInstaller;
use EzSystems\PlatformInstallerBundle\Installer\DbBasedInstaller;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

/**
 * @covers \EzSystems\PlatformInstallerBundle\DependencyInjection\EzSystemsPlatformInstallerExtension
 */
class EzSystemsPlatformInstallerExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @covers \EzSystems\PlatformInstallerBundle\DependencyInjection\EzSystemsPlatformInstallerExtension::load
     */
    public function testLoadLoadsTaggedCoreInstaller(): void
    {
        $this->load();
        $this->assertContainerBuilderHasServiceDefinitionWithParent(
            CoreInstaller::class,
            DbBasedInstaller::class
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            CoreInstaller::class,
            InstallerTagPass::INSTALLER_TAG,
            ['type' => 'clean']
        );
    }

    /**
     * @covers \EzSystems\PlatformInstallerBundle\DependencyInjection\EzSystemsPlatformInstallerExtension::load
     */
    public function testLoadLoadsTaggedInstallerCommand(): void
    {
        $this->load();
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            InstallPlatformCommand::class,
            'console.command',
            ['command' => 'ezplatform:install']
        );
    }

    protected function getContainerExtensions(): array
    {
        return [
            new EzSystemsPlatformInstallerExtension(),
        ];
    }
}
