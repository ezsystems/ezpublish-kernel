<?php

/**
 * File containing the Contextualizer class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Contextualizer implements ContextualizerInterface
{
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    /** @var string */
    private $namespace;

    /**
     * Name of the node under which scope based (semantic) configuration takes place.
     *
     * @var string
     */
    private $siteAccessNodeName;

    /** @var array */
    private $availableSiteAccesses;

    /** @var array */
    private $availableSiteAccessGroups;

    /** @var array */
    private $groupsBySiteAccess;

    public function __construct(
        ContainerInterface $containerBuilder,
        $namespace,
        $siteAccessNodeName,
        array $availableSiteAccesses,
        array $availableSiteAccessGroups,
        array $groupsBySiteAccess
    ) {
        $this->container = $containerBuilder;
        $this->namespace = $namespace;
        $this->siteAccessNodeName = $siteAccessNodeName;
        $this->availableSiteAccesses = $availableSiteAccesses;
        $this->availableSiteAccessGroups = $availableSiteAccessGroups;
        $this->groupsBySiteAccess = $groupsBySiteAccess;
    }

    public function setContextualParameter($parameterName, $scope, $value)
    {
        $this->container->setParameter("$this->namespace.$scope.$parameterName", $value);
    }

    public function mapSetting($id, array $config)
    {
        foreach ($config[$this->siteAccessNodeName] as $currentScope => $scopeSettings) {
            if (isset($scopeSettings[$id])) {
                $this->setContextualParameter($id, $currentScope, $scopeSettings[$id]);
            }
        }
    }

    public function mapConfigArray($id, array $config, $options = 0)
    {
        $this->mapReservedScopeArray($id, $config, ConfigResolver::SCOPE_DEFAULT);
        $this->mapReservedScopeArray($id, $config, ConfigResolver::SCOPE_GLOBAL);
        $defaultSettings = $this->getContainerParameter(
            $this->namespace . '.' . ConfigResolver::SCOPE_DEFAULT . '.' . $id,
            []
        );
        $globalSettings = $this->getContainerParameter(
            $this->namespace . '.' . ConfigResolver::SCOPE_GLOBAL . '.' . $id,
            []
        );

        // (!) Keep siteaccess group settings
        foreach (array_keys($this->availableSiteAccessGroups) as $scope) {
            $scopeSettings = $config[$this->siteAccessNodeName][$scope][$id] ?? [];
            if (empty($scopeSettings)) {
                continue;
            }
            if ($options & static::MERGE_FROM_SECOND_LEVEL) {
                $mergedSettings = [];

                // array_merge() has to be used because we don't
                // know whether we have a hash or a plain array
                $keys = array_unique(
                    array_merge(
                        array_keys($defaultSettings),
                        array_keys($scopeSettings),
                        array_keys($globalSettings)
                    )
                );
                foreach ($keys as $key) {
                    // Only merge if actual setting is an array.
                    // We assume default setting to be a clear reference for this.
                    // If the setting is not an array, we copy the right value, in respect to the precedence:
                    // 1. global
                    // 3. Group
                    // 4. default
                    if (array_key_exists($key, $defaultSettings) && !is_array($defaultSettings[$key])) {
                        if (array_key_exists($key, $globalSettings)) {
                            $mergedSettings[$key] = $globalSettings[$key];
                        } elseif (array_key_exists($key, $scopeSettings)) {
                            $mergedSettings[$key] = $scopeSettings[$key];
                        } else {
                            $mergedSettings[$key] = $defaultSettings[$key];
                        }
                    } else {
                        $mergedSettings[$key] = array_merge(
                            $defaultSettings[$key] ?? [],
                            $scopeSettings[$key] ?? [],
                            $globalSettings[$key] ?? []
                        );
                    }
                }
            } else {
                $mergedSettings = array_merge(
                    $defaultSettings,
                    $scopeSettings,
                    $globalSettings
                );
            }
            if ($options & static::UNIQUE) {
                $mergedSettings = array_values(
                    array_unique($mergedSettings)
                );
            }
            $this->container->setParameter("$this->namespace.$scope.$id", $mergedSettings);
        }

        foreach ($this->availableSiteAccesses as $scope) {
            // for a siteaccess, we have to merge the default value,
            // the group value(s), the siteaccess value and the global
            // value of the settings.
            $groupsSettings = [];
            if (isset($this->groupsBySiteAccess[$scope]) && is_array($this->groupsBySiteAccess[$scope])) {
                $groupsSettings = $this->groupsArraySetting(
                    $this->groupsBySiteAccess[$scope],
                    $id,
                    $config,
                    $options & static::MERGE_FROM_SECOND_LEVEL
                );
            }

            $scopeSettings = [];
            if (isset($config[$this->siteAccessNodeName][$scope][$id])) {
                $scopeSettings = $config[$this->siteAccessNodeName][$scope][$id];
            }

            if (empty($groupsSettings) && empty($scopeSettings)) {
                continue;
            }

            if ($options & static::MERGE_FROM_SECOND_LEVEL) {
                // array_merge() has to be used because we don't
                // know whether we have a hash or a plain array
                $keys1 = array_unique(
                    array_merge(
                        array_keys($defaultSettings),
                        array_keys($groupsSettings),
                        array_keys($scopeSettings),
                        array_keys($globalSettings)
                    )
                );
                $mergedSettings = [];
                foreach ($keys1 as $key) {
                    // Only merge if actual setting is an array.
                    // We assume default setting to be a clear reference for this.
                    // If the setting is not an array, we copy the right value, in respect to the precedence:
                    // 1. global
                    // 2. SiteAccess
                    // 3. Group
                    // 4. default
                    if (array_key_exists($key, $defaultSettings) && !is_array($defaultSettings[$key])) {
                        if (array_key_exists($key, $globalSettings)) {
                            $mergedSettings[$key] = $globalSettings[$key];
                        } elseif (array_key_exists($key, $scopeSettings)) {
                            $mergedSettings[$key] = $scopeSettings[$key];
                        } elseif (array_key_exists($key, $groupsSettings)) {
                            $mergedSettings[$key] = $groupsSettings[$key];
                        } else {
                            $mergedSettings[$key] = $defaultSettings[$key];
                        }
                    } else {
                        $mergedSettings[$key] = array_merge(
                            isset($defaultSettings[$key]) ? $defaultSettings[$key] : [],
                            isset($groupsSettings[$key]) ? $groupsSettings[$key] : [],
                            isset($scopeSettings[$key]) ? $scopeSettings[$key] : [],
                            isset($globalSettings[$key]) ? $globalSettings[$key] : []
                        );
                    }
                }
            } else {
                $mergedSettings = array_merge(
                    $defaultSettings,
                    $groupsSettings,
                    $scopeSettings,
                    $globalSettings
                );
            }

            if ($options & static::UNIQUE) {
                $mergedSettings = array_values(
                    array_unique($mergedSettings)
                );
            }

            $this->container->setParameter("$this->namespace.$scope.$id", $mergedSettings);
        }
    }

    /**
     * Returns the value under the $id in the $container. if the container does
     * not known this $id, returns $default.
     *
     * @param string $id
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getContainerParameter($id, $default = null)
    {
        if ($this->container->hasParameter($id)) {
            return $this->container->getParameter($id);
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
    private function groupsArraySetting(array $groups, $id, array $config, $options = 0)
    {
        $groupsSettings = [];
        sort($groups);
        foreach ($groups as $group) {
            if (isset($config[$this->siteAccessNodeName][$group][$id])) {
                if ($options & static::MERGE_FROM_SECOND_LEVEL) {
                    foreach (array_keys($config[$this->siteAccessNodeName][$group][$id]) as $key) {
                        if (!isset($groupsSettings[$key])) {
                            $groupsSettings[$key] = $config[$this->siteAccessNodeName][$group][$id][$key];
                        } else {
                            // array_merge() has to be used because we don't
                            // know whether we have a hash or a plain array
                            $groupsSettings[$key] = array_merge(
                                $groupsSettings[$key],
                                $config[$this->siteAccessNodeName][$group][$id][$key]
                            );
                        }
                    }
                } else {
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
     * Ensures settings array defined in a given "reserved scope" are registered properly.
     * "Reserved scope" can typically be ConfigResolver::SCOPE_DEFAULT or ConfigResolver::SCOPE_GLOBAL.
     *
     * @param string $id
     * @param array $config
     * @param string $scope
     */
    private function mapReservedScopeArray($id, array $config, $scope)
    {
        if (
            isset($config[$this->siteAccessNodeName][$scope][$id])
            && !empty($config[$this->siteAccessNodeName][$scope][$id])
        ) {
            $key = "$this->namespace.$scope.$id";
            $value = $config[$this->siteAccessNodeName][$scope][$id];
            if ($this->container->hasParameter($key)) {
                $value = array_merge(
                    $this->container->getParameter($key),
                    $value
                );
            }
            $this->container->setParameter($key, $value);
        }
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setSiteAccessNodeName($scopeNodeName)
    {
        $this->siteAccessNodeName = $scopeNodeName;
    }

    public function getSiteAccessNodeName()
    {
        return $this->siteAccessNodeName;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setAvailableSiteAccesses(array $availableSiteAccesses)
    {
        $this->availableSiteAccesses = $availableSiteAccesses;
    }

    public function getAvailableSiteAccesses()
    {
        return $this->availableSiteAccesses;
    }

    public function setGroupsBySiteAccess(array $groupsBySiteAccess)
    {
        $this->groupsBySiteAccess = $groupsBySiteAccess;
    }

    public function getGroupsBySiteAccess()
    {
        return $this->groupsBySiteAccess;
    }
}
