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
        if (!$container->hasAlias(SchemaBuilder::class)) {
            $container->removeDefinition(CoreInstaller::class);
            @trigger_error(
                sprintf(
                    'Using eZ Platform Installer Bundle without enabling Doctrine Schema Bundle (%s) ' .
                    'is deprecated since v2.5 LTS and will cause a fatal error in eZ Platform v3.0',
                    'https://github.com/ezsystems/doctrine-dbal-schema'
                ),
                E_USER_DEPRECATED
            );
        } elseif ($container->hasDefinition(self::CLEAN_INSTALLER_DEF_ID)) {
            // remove the actual definition first for alias to work properly
            $container->removeDefinition(self::CLEAN_INSTALLER_DEF_ID);
            $container->setAlias(self::CLEAN_INSTALLER_DEF_ID, CoreInstaller::class);
        }
    }
}
