<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\PlatformInstallerBundle\DependencyInjection\Compiler;

use EzSystems\DoctrineSchema\API\Builder\SchemaBuilder;
use EzSystems\PlatformInstallerBundle\Installer\CoreInstaller;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Enable installer which uses SchemaBuilder.
 *
 * <code>DoctrineSchemaBundle</code> is available via <code>ezsystems/doctrine-dbal-schema</code> package.
 *
 * @see \EzSystems\DoctrineSchemaBundle\DoctrineSchemaBundle
 * @see \EzSystems\PlatformInstallerBundle\Installer\CoreInstaller
 */
class SchemaBuilderInstallerPass implements CompilerPassInterface
{
    const CLEAN_INSTALLER_DEF_ID = 'ezplatform.installer.clean_installer';

    /**
     * Replace Clean installer with CoreInstaller if required SchemaBuilder from DoctrineSchemaBundle is available.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasAlias(SchemaBuilder::class)
            || !$container->hasDefinition(self::CLEAN_INSTALLER_DEF_ID)
        ) {
            return;
        }

        $cleanInstallerDefinition = $container->getDefinition(self::CLEAN_INSTALLER_DEF_ID);
        $cleanInstallerDefinition->setClass(CoreInstaller::class);
        $cleanInstallerDefinition->addArgument(new Reference(SchemaBuilder::class));
    }
}
