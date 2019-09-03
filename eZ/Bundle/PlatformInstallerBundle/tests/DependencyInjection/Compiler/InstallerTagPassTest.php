<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\PlatformInstallerBundleTests\DependencyInjection\Compiler;

use EzSystems\PlatformInstallerBundle\Command\InstallPlatformCommand;
use EzSystems\PlatformInstallerBundle\DependencyInjection\Compiler\InstallerTagPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \EzSystems\PlatformInstallerBundle\DependencyInjection\Compiler\InstallerTagPass
 */
class InstallerTagPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @covers \EzSystems\PlatformInstallerBundle\DependencyInjection\Compiler\InstallerTagPass::process
     */
    public function testProcessInjectsInstallersIntoCommand(): void
    {
        $this->setDefinition(
            InstallPlatformCommand::class,
            new Definition(InstallPlatformCommand::class, ['$installers' => []])
        );
        $definition = new Definition();
        $definition->addTag(
            InstallerTagPass::INSTALLER_TAG,
            [
                'type' => 'installer_type',
            ]
        );

        $this->setDefinition('service_id', $definition);
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            InstallPlatformCommand::class,
            '$installers',
            [
                'installer_type' => new Reference('service_id'),
            ]
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new InstallerTagPass());
    }
}
