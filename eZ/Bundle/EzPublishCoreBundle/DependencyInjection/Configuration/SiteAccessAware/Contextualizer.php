<?php
/**
 * File containing the Contextualizer class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Contextualizer implements ContextualizerInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $namespace;

    /**
     * Name of the node under which scope based (semantic) configuration takes place.
     *
     * @var string
     */
    private $scopeNodeName;

    /**
     * @var array
     */
    private $availableScopes;

    /**
     * @var array
     */
    private $groupsByScope;

    public function __construct(
        ContainerInterface $containerBuilder,
        $namespace,
        $scopeNodeName,
        array $availableScopes,
        array $groupsByScope
    )
    {
        $this->container = $containerBuilder;
        $this->namespace = $namespace;
        $this->scopeNodeName = $scopeNodeName;
        $this->availableScopes = $availableScopes;
        $this->groupsByScope = $groupsByScope;
    }

    /**
     * Defines a contextual parameter in the container, with the appropriate format, i.e. <namespace>.<scope>.<parameter_name>.
     *
     * ```php
     * <?php
     * namespace Acme\DemoBundle\DependencyInjection;
     *
     * use Symfony\Component\HttpKernel\DependencyInjection\Extension;
     * use Symfony\Component\DependencyInjection\ContainerBuilder;
     * use Symfony\Component\DependencyInjection\Loader;
     * use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;
     *
     * class AcmeDemoExtension extends Extension
     * {
     *     public function load( array $configs, ContainerBuilder $container )
     *     {
     *         $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );
     *
     *         $configuration = $this->getConfiguration( $configs, $container );
     *         $config = $this->processConfiguration( $configuration, $configs );
     *
     *         // ...
     *         $processor = new SiteAccessAware\ConfigurationProcessor( $container, 'acme_demo' );
     *         $processor->mapConfig(
     *             $config,
     *             function ( array $scopeSettings, $currentScope, SiteAccessAware\ContextualizerInterface $contextualizer )
     *             {
     *                 $contextualizer->setContextualParameter( 'my_internal_parameter', $currentScope, $scopeSettings['some_semantic_parameter'] );
     *             }
     *         );
     *     }
     * }
     * ```
     *
     * @param string $parameterName
     * @param string $scope
     * @param mixed $value
     */
    public function setContextualParameter( $parameterName, $scope, $value )
    {
        $this->container->setParameter( "$this->namespace.$scope.$parameterName", $value );
    }

    /**
     * Maps semantic array settings to internal format, and merges them between scopes.
     *
     * This is useful when you have e.g. a hash of settings defined in a siteaccess group and you want an entry of
     * this hash, defined at the siteaccess or global level, to replace the one in the group.
     *
     * Defined arrays are merged in the following scopes:
     *
     * * `default`
     * * siteaccess groups
     * * siteaccess
     * * `global`
     *
     * To calculate the precedence of siteaccess groups, they are alphabetically sorted.
     *
     * Example:
     *
     * ```yaml
     * acme_demo:
     *     system:
     *         my_siteaccess_group:
     *             foo_setting:
     *                 foo: "bar"
     *                 some: "thing"
     *                 an_integer: 123
     *                 enabled: false
     *
     *         # Assuming my_siteaccess is part of my_siteaccess_group
     *         my_siteaccess:
     *             foo_setting:
     *                 an_integer: 456
     *                 enabled: true
     * ```
     *
     * In your DIC extension
     *
     * ```php
     * namespace Acme\DemoBundle\DependencyInjection;
     *
     * use Symfony\Component\HttpKernel\DependencyInjection\Extension;
     * use Symfony\Component\DependencyInjection\ContainerBuilder;
     * use Symfony\Component\DependencyInjection\Loader;
     * use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;
     *
     * class AcmeDemoExtension extends Extension
     * {
     *     public function load( array $configs, ContainerBuilder $container )
     *     {
     *         $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );
     *
     *         $configuration = $this->getConfiguration( $configs, $container );
     *         $config = $this->processConfiguration( $configuration, $configs );
     *
     *         // ...
     *         $processor = new SiteAccessAware\ConfigurationProcessor( $container, 'acme_demo' );
     *         $contextualizer = $processor->getContextualizer();
     *         $contextualizer->mapConfigArray( 'foo_setting', $configs );
     *
     *         $processor->mapConfig(
     *             $config,
     *             function ( array $scopeSettings, $currentScope, SiteAccessAware\ContextualizerInterface $contextualizer )
     *             {
     *                 // ...
     *             }
     *         );
     *     }
     * }
     * ```
     *
     * This will result with having following parameters in the container:
     *
     * ```yaml
     * acme_demo.my_siteaccess.foo_setting:
     *     foo: "bar"
     *     some: "thing"
     *     an_integer: 456
     *     enabled: true
     *
     * acme_demo.my_siteaccess_gorup.foo_setting
     *     foo: "bar"
     *         some: "thing"
     *         an_integer: 123
     *         enabled: false
     * ```
     *
     * @param string $id Id of the setting array to map.
     *                   Note that it will be used to identify the semantic setting in $config and to define the internal
     *                   setting in the container (<namespace>.<scope>.<$id>)
     * @param array $config Full semantic configuration array for current bundle.
     * @param int $options Bit mask of options (@see constants of this class)
     */
    public function mapConfigArray( $id, array $config, $options = 0 )
    {
        $this->mapGlobalConfigArray( $id, $config );
        $defaultSettings = $this->getContainerParameter(
            $this->namespace . '.' . ConfigResolver::SCOPE_DEFAULT . '.' . $id,
            array()
        );
        $globalSettings = $this->getContainerParameter(
            $this->namespace . '.' . ConfigResolver::SCOPE_GLOBAL . '.' . $id,
            array()
        );

        foreach ( $this->availableScopes as $scope )
        {
            // for a siteaccess, we have to merge the default value,
            // the group value(s), the siteaccess value and the global
            // value of the settings.
            $groupsSettings = array();
            if ( isset( $this->groupsByScope[$scope] ) && is_array( $this->groupsByScope[$scope] ) )
            {
                $groupsSettings = $this->groupsArraySetting(
                    $this->groupsByScope[$scope], $id,
                    $config, $options & static::MERGE_FROM_SECOND_LEVEL
                );
            }

            $scopeSettings = array();
            if ( isset( $config[$this->scopeNodeName][$scope][$id] ) )
            {
                $scopeSettings = $config[$this->scopeNodeName][$scope][$id];
            }

            if ( $options & static::MERGE_FROM_SECOND_LEVEL )
            {
                // array_merge() has to be used because we don't
                // know whether we have a hash or a plain array
                $keys1 = array_unique(
                    array_merge(
                        array_keys( $defaultSettings ),
                        array_keys( $groupsSettings ),
                        array_keys( $scopeSettings ),
                        array_keys( $globalSettings )
                    )
                );
                $mergedSettings = array();
                foreach ( $keys1 as $key )
                {
                    $mergedSettings[$key] = array_merge(
                        isset( $defaultSettings[$key] ) ? $defaultSettings[$key] : array(),
                        isset( $groupsSettings[$key] ) ? $groupsSettings[$key] : array(),
                        isset( $scopeSettings[$key] ) ? $scopeSettings[$key] : array(),
                        isset( $globalSettings[$key] ) ? $globalSettings[$key] : array()
                    );
                }
            }
            else
            {
                $mergedSettings = array_merge(
                    $defaultSettings,
                    $groupsSettings,
                    $scopeSettings,
                    $globalSettings
                );
            }

            if ( $options & static::UNIQUE )
            {
                $mergedSettings = array_values(
                    array_unique( $mergedSettings )
                );
            }

            $this->container->setParameter( "$this->namespace.$scope.$id", $mergedSettings );
        }
    }

    /**
     * Returns the value under the $id in the $container. if the container does
     * not known this $id, returns $default
     *
     * @param string $id
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getContainerParameter( $id, $default = null )
    {
        if ( $this->container->hasParameter( $id ) )
        {
            return $this->container->getParameter( $id );
        }

        return $default;
    }

    /**
     * Merges setting array for a set of groups.
     *
     * @param array $groups array of group name
     * @param string $id id of the setting array under ezpublish.<base_key>.<group_name>
     * @param array $config the full configuration array
     * @param int $options only static::MERGE_FROM_SECOND_LEVEL or static::UNIQUE are recognized
     *
     * @return array
     */
    private function groupsArraySetting( array $groups, $id, array $config, $options = 0 )
    {
        $groupsSettings = array();
        sort( $groups );
        foreach ( $groups as $group )
        {
            if ( isset( $config[$this->scopeNodeName][$group][$id] ) )
            {
                if ( $options & static::MERGE_FROM_SECOND_LEVEL )
                {
                    foreach ( array_keys( $config[$this->scopeNodeName][$group][$id] ) as $key )
                    {
                        if ( !isset( $groupsSettings[$key] ) )
                        {
                            $groupsSettings[$key] = $config[$this->scopeNodeName][$group][$id][$key];
                        }
                        else
                        {
                            // array_merge() has to be used because we don't
                            // know whether we have a hash or a plain array
                            $groupsSettings[$key] = array_merge(
                                $groupsSettings[$key],
                                $config[$this->scopeNodeName][$group][$id][$key]
                            );
                        }
                    }
                }
                else
                {
                    // array_merge() has to be used because we don't
                    // know whether we have a hash or a plain array
                    $groupsSettings = array_merge(
                        $groupsSettings,
                        $config[$this->scopeNodeName][$group][$id]
                    );
                }
            }
        }
        return $groupsSettings;
    }

    /**
     * Ensures settings array defined in "global" scope are registered in the internal global scope.
     *
     * @param string $id
     * @param array $config
     */
    private function mapGlobalConfigArray( $id, array $config )
    {
        if (
            isset( $config[$this->scopeNodeName][ConfigResolver::SCOPE_GLOBAL][$id] )
            && !empty( $config[$this->scopeNodeName][ConfigResolver::SCOPE_GLOBAL][$id] )
        )
        {
            $key = $this->namespace . '.' . ConfigResolver::SCOPE_GLOBAL . '.' . $id;
            $globalValue = $config[$this->scopeNodeName][ConfigResolver::SCOPE_GLOBAL][$id];
            if ( $this->container->hasParameter( $key ) )
            {
                $globalValue = array_merge(
                    $this->container->getParameter( $key ),
                    $globalValue
                );
            }
            $this->container->setParameter( $key, $globalValue );
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer( ContainerInterface $container )
    {
        $this->container = $container;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param string $scopeNodeName
     */
    public function setScopeNodeName( $scopeNodeName )
    {
        $this->scopeNodeName = $scopeNodeName;
    }

    /**
     * @return string
     */
    public function getScopeNodeName()
    {
        return $this->scopeNodeName;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace( $namespace )
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param array $availableScopes
     */
    public function setAvailableScopes( array $availableScopes )
    {
        $this->availableScopes = $availableScopes;
    }

    /**
     * @return array
     */
    public function getAvailableScopes()
    {
        return $this->availableScopes;
    }

    /**
     * @param array $groupsByScope
     */
    public function setGroupsByScope( array $groupsByScope )
    {
        $this->groupsByScope = $groupsByScope;
    }

    /**
     * @return array
     */
    public function getGroupsByScope()
    {
        return $this->groupsByScope;
    }
}
