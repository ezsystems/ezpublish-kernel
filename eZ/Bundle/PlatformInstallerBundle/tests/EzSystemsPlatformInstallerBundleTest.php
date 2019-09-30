<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\PlatformInstallerBundleTests;

use EzSystems\DoctrineSchemaBundle\DependencyInjection\DoctrineSchemaExtension;
use EzSystems\PlatformInstallerBundle\DependencyInjection\Compiler\InstallerTagPass;
use EzSystems\PlatformInstallerBundle\EzSystemsPlatformInstallerBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class EzSystemsPlatformInstallerBundleTest extends TestCase
{
    /** @var \EzSystems\PlatformInstallerBundle\EzSystemsPlatformInstallerBundle */
    private $bundle;

    public function setUp(): void
    {
        $this->bundle = new EzSystemsPlatformInstallerBundle();
    }

    /**
     * @covers \EzSystems\PlatformInstallerBundle\EzSystemsPlatformInstallerBundle::build
     */
    public function testBuild(): void
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new DoctrineSchemaExtension());
        $this->bundle->build($container);

        // check if InstallerTagPass was added
        self::assertNotEmpty(
            array_filter(
                $container->getCompilerPassConfig()->getPasses(),
                function (CompilerPassInterface $compilerPass) {
                    return $compilerPass instanceof InstallerTagPass;
                }
            )
        );
    }

    /**
     * @covers \EzSystems\PlatformInstallerBundle\EzSystemsPlatformInstallerBundle::build
     */
    public function testBuildFailsWithoutDoctrineSchemaBundle(): void
    {
        $container = new ContainerBuilder();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('eZ Platform Installer requires Doctrine Schema Bundle');
        $this->bundle->build($container);
    }
}
