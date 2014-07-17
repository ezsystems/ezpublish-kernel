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
    private $siteAccessNodeName;

    /**
     * @var array
     */
    private $availableSiteAccesses;

    /**
     * @var array
     */
    private $groupsBySiteAccess;

    public function __construct(
        ContainerInterface $containerBuilder,
        $namespace,
        $siteAccessNodeName,
        array $availableSiteAccesses,
        array $groupsBySiteAccess
    )
    {
        $this->container = $containerBuilder;
        $this->namespace = $namespace;
        $this->siteAccessNodeName = $siteAccessNodeName;
        $this->availableSiteAccesses = $availableSiteAccesses;
        $this->groupsBySiteAccess = $groupsBySiteAccess;
    }

    public function setContextualParameter( $parameterName, $scope, $value )
    {
        $this->container->setParameter( "$this->namespace.$scope.$parameterName", $value );
    }

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

        foreach ( $this->availableSiteAccesses as $scope )
        {
            // for a siteaccess, we have to merge the default value,
            // the group value(s), the siteaccess value and the global
            // value of the settings.
            $groupsSettings = array();
            if ( isset( $this->groupsBySiteAccess[$scope] ) && is_array( $this->groupsBySiteAccess[$scope] ) )
            {
                $groupsSettings = $this->groupsArraySetting(
                    $this->groupsBySiteAccess[$scope], $id,
                    $config, $options & static::MERGE_FROM_SECOND_LEVEL
                );
            }

            $scopeSettings = array();
            if ( isset( $config[$this->siteAccessNodeName][$scope][$id] ) )
            {
                $scopeSettings = $config[$this->siteAccessNodeName][$scope][$id];
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
            if ( isset( $config[$this->siteAccessNodeName][$group][$id] ) )
            {
                if ( $options & static::MERGE_FROM_SECOND_LEVEL )
                {
                    foreach ( array_keys( $config[$this->siteAccessNodeName][$group][$id] ) as $key )
                    {
                        if ( !isset( $groupsSettings[$key] ) )
                        {
                            $groupsSettings[$key] = $config[$this->siteAccessNodeName][$group][$id][$key];
                        }
                        else
                        {
                            // array_merge() has to be used because we don't
                            // know whether we have a hash or a plain array
                            $groupsSettings[$key] = array_merge(
                                $groupsSettings[$key],
                                $config[$this->siteAccessNodeName][$group][$id][$key]
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
                        $config[$this->siteAccessNodeName][$group][$id]
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
            isset( $config[$this->siteAccessNodeName][ConfigResolver::SCOPE_GLOBAL][$id] )
            && !empty( $config[$this->siteAccessNodeName][ConfigResolver::SCOPE_GLOBAL][$id] )
        )
        {
            $key = $this->namespace . '.' . ConfigResolver::SCOPE_GLOBAL . '.' . $id;
            $globalValue = $config[$this->siteAccessNodeName][ConfigResolver::SCOPE_GLOBAL][$id];
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

    public function setContainer( ContainerInterface $container )
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setSiteAccessNodeName( $scopeNodeName )
    {
        $this->siteAccessNodeName = $scopeNodeName;
    }

    public function getSiteAccessNodeName()
    {
        return $this->siteAccessNodeName;
    }

    public function setNamespace( $namespace )
    {
        $this->namespace = $namespace;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setAvailableSiteAccesses( array $availableSiteAccesses )
    {
        $this->availableSiteAccesses = $availableSiteAccesses;
    }

    public function getAvailableSiteAccesses()
    {
        return $this->availableSiteAccesses;
    }

    public function setGroupsBySiteAccess( array $groupsBySiteAccess )
    {
        $this->groupsBySiteAccess = $groupsBySiteAccess;
    }

    public function getGroupsBySiteAccess()
    {
        return $this->groupsBySiteAccess;
    }
}
