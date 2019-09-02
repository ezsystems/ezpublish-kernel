<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\PlatformInstallerBundle\DependencyInjection\Compiler;

use EzSystems\DoctrineSchema\API\Builder\SchemaBuilder;
use EzSystems\PlatformInstallerBundle\Installer\CoreInstaller;
use EzSystems\PlatformInstallerBundle\Installer\DbBasedInstaller;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

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
    const DB_BASED_INSTALLER_DEF_ID = 'ezplatform.installer.db_based_installer';

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

        $this->warnAboutRelyingOnDeprecatedService(
            $container,
            [
                self::DB_BASED_INSTALLER_DEF_ID => DbBasedInstaller::class,
                self::CLEAN_INSTALLER_DEF_ID => CoreInstaller::class,
            ]
        );
    }

    /**
     * Find usages of deprecated service definitions in custom services.
     *
     * Note: natural choice would be to use `deprecated` attribute in DIC instead, however
     * in Symfony 3.4 neither deprecating service alias nor deprecating abstract service gives
     * proper warning in the application logs.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string[] a map of old to new names (associative array)
     */
    private function warnAboutRelyingOnDeprecatedService(
        ContainerBuilder $container,
        array $oldNewNameMap
    ): void {
        // find usages of deprecated definitions as parents
        /** @var \Symfony\Component\DependencyInjection\ChildDefinition[] $deprecatedParentDefinitions */
        $deprecatedParentDefinitions = array_filter(
            $container->getDefinitions(),
            function (Definition $definition) use ($oldNewNameMap) {
                return $definition instanceof ChildDefinition
                    && array_key_exists($definition->getParent(), $oldNewNameMap);
            }
        );
        // trigger deprecation warnings to be logged
        foreach ($deprecatedParentDefinitions as $id => $definition) {
            $parent = $definition->getParent();
            @trigger_error(
                sprintf(
                    'The service definition "%s" relies on the deprecated "%s" service. Use "%s" instead',
                    $id,
                    $parent,
                    $oldNewNameMap[$parent]
                ),
                E_USER_DEPRECATED
            );
        }
    }
}
