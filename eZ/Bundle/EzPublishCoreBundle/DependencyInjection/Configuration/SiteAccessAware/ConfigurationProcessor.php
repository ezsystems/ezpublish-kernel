<?php
/**
 * File containing the ScopeConfigurationProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Closure;

class ConfigurationProcessor
{
    /**
     * With this option, registerInternalConfigArray() will call array_unique() at the end of the merge process.
     * This will only work with normal arrays (i.e. not hashes) containing scalar values.
     */
    const UNIQUE = 1;

    /**
     * With this option, registerInternalConfigArray() will merge the hashes from the second level.
     * For instance:
     * array( 'full' => array( 1, 2, 3 ) ) and array( 'full' => array( 4, 5 ) )
     * will result in array( 'full' => array( 1, 2, 3, 4, 5 ) )
     */
    const MERGE_FROM_SECOND_LEVEL = 2;

    /**
     * Registered configuration scopes.
     *
     * @var array
     */
    static protected $scopes;

    /**
     * Registered scope groups names, indexed by scope.
     *
     * @var array
     */
    static protected $groupsByScope;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * Namespace for internal settings.
     * Registered internal settings always have the format <namespace>.<scope>.<parameter_name>
     * e.g. ezsettings.default.session
     *
     * @var string
     */
    protected $namespace;

    /**
     * Name of the node under which scope based (semantic) configuration takes place.
     *
     * @var string
     */
    protected $scopeNodeName;

    public function __construct( ContainerInterface $containerBuilder, $namespace, $scopeNodeName = 'system' )
    {
        $this->container = $containerBuilder;
        $this->namespace = $namespace;
        $this->scopeNodeName = $scopeNodeName;
    }

    /**
     * Injects available configuration scopes.
     *
     * @param array $scopes
     */
    static public function setScopes( array $scopes )
    {
        static::$scopes = $scopes;
    }

    /**
     * Injects available scope groups, indexed by scope.
     *
     * @param array $groupsByScope
     */
    static public function setGroupsByScope( array $groupsByScope )
    {
        static::$groupsByScope = $groupsByScope;
    }

    /**
     * Returns internal ContainerBuilder instance.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param array $config
     * @param ConfigurationMapper|callable $mapper
     *
     * @throws \InvalidArgumentException
     */
    public function mapConfig( array $config, $mapper )
    {
        if ( $mapper instanceof HookableMapper )
        {
            $mapper->preMap( $config, $this );
        }

        foreach ( $config[$this->scopeNodeName] as $scope => &$settings )
        {
            if ( is_callable( $mapper ) )
            {
                call_user_func_array( $mapper, array( $settings, $scope, $this ) );
            }
            else if ( $mapper instanceof ConfigurationMapper )
            {
                $mapper->mapConfig( $settings, $scope, $this );
            }
            else
            {
                throw new \InvalidArgumentException( 'prout' );
            }
        }

        if ( $mapper instanceof HookableMapper )
        {
            $mapper->postMap( $config, $this );
        }
    }

    /**
     * Registers given parameter in container for given scope, in current namespace.
     * Resulting parameter will have format <namespace>.<scope>.<parameterName> .
     *
     * @param string $parameterName
     * @param mixed $value
     * @param string $scope
     */
    public function setParameter( $parameterName, $value, $scope )
    {
        $this->container->setParameter( "$this->namespace.$scope.$parameterName", $value );
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
     * Registers and merges the internal scope configuration for array settings.
     * We merge arrays defined in scopes "default", in scope groups, in the scope itself and in the "global" scope.
     * To calculate the precedence of scope groups, they are alphabetically sorted.
     *
     * One may call this method from inside config parser's preScopeConfig() or postScopeConfig() method.
     *
     * @param string $id id of the setting array to register
     * @param array $config the full configuration as an array
     * @param int $options bit mask of options (@see constants of this class)
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

        foreach ( static::$scopes as $scope )
        {
            // for a siteaccess, we have to merge the default value,
            // the group value(s), the siteaccess value and the global
            // value of the settings.
            $groupsSettings = array();
            if ( isset( static::$groupsByScope[$scope] ) && is_array( static::$groupsByScope[$scope] ) )
            {
                $groupsSettings = $this->groupsArraySetting(
                    static::$groupsByScope[$scope], $id,
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
}
