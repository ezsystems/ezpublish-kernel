<?php
/**
 * File containing the AbstractParser parser.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Provides helpers to deal with array settings. This abstract class mainly
 * provides the registerInternalConfigArray() method to properly merge and
 * register array settings defined at in several scopes:
 * <code>
 *     $this->registerInternalConfigArray( 'key', $config, $container );
 * </code>
 * This method will look for the 'key' array in the configuration provided in
 * $config and will merge the arrays from default, group, siteaccess and global
 * scopes in the correct order to compute the value for each siteaccess.
 */
abstract class AbstractParser implements Parser
{
    /**
     * With this option, AbstractParser::registerInternalConfigArray() will
     * call array_unique() at the end of the merge process. This will only work
     * with normal arrays (ie not hashes) containing scalar values.
     */
    const UNIQUE = 1;

    /**
     * With this option, AbstractParser::registerInternalConfigArray() will
     * merge the hashes from the second level. For instance:
     * array( 'full' => array( 1, 2, 3 ) ) and array( 'full' => array( 4, 5 ) )
     * will result in array( 'full' => array( 1, 2, 3, 4, 5 ) )
     */
    const MERGE_FROM_SECOND_LEVEL = 2;

    /**
     * The base key where to look for the configuration. 'system' by default.
     *
     * @var string
     */
    protected $baseKey = 'system';

    /**
     * Sets the base key of this parser.
     *
     * @param string $key
     */
    public function setBaseKey( $key )
    {
        $this->baseKey = $key;
    }

    /**
     * Returns the value under the $id in the $container. if the container does
     * not known this $id, returns $default
     *
     * @param ContainerBuilder $container
     * @param string $id
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getContainerParameter( ContainerBuilder $container, $id, $default = null )
    {
        if ( $container->hasParameter( $id ) )
        {
            return $container->getParameter( $id );
        }
        return $default;
    }

    /**
     * Merges setting array for a set of groups.
     *
     * @param array $groups array of group name
     * @param string $id id of the setting array under ezpublish.<base_key>.<group_name>
     * @param array $config the full configuration array
     * @param int $options only self::MERGE_FROM_SECOND_LEVEL or self::UNIQUE are recognized
     *
     * @return array
     */
    protected function groupsArraySetting( array $groups, $id, array $config, $options = 0 )
    {
        $groupsSettings = array();
        sort( $groups );
        foreach ( $groups as $group )
        {
            if ( isset( $config[$this->baseKey][$group][$id] ) )
            {
                if ( $options & self::MERGE_FROM_SECOND_LEVEL )
                {
                    foreach ( array_keys( $config[$this->baseKey][$group][$id] ) as $key )
                    {
                        if ( !isset( $groupsSettings[$key] ) )
                        {
                            $groupsSettings[$key] = $config[$this->baseKey][$group][$id][$key];
                        }
                        else
                        {
                            // array_merge() has to be used because we don't
                            // know whether we have a hash or a plain array
                            $groupsSettings[$key] = array_merge(
                                $groupsSettings[$key],
                                $config[$this->baseKey][$group][$id][$key]
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
                        $config[$this->baseKey][$group][$id]
                    );
                }
            }
        }
        return $groupsSettings;
    }

    /**
     * Make sure settings array defined in "global" siteaccess are registered
     * in the internal global scope.
     *
     * @param string $id
     * @param array $config
     * @param ContainerBuilder $container
     */
    protected function registerGlobalConfigArray( $id, array $config, ContainerBuilder $container )
    {
        if ( isset( $config[$this->baseKey][ConfigResolver::SCOPE_GLOBAL][$id] )
            && !empty( $config[$this->baseKey][ConfigResolver::SCOPE_GLOBAL][$id] ) )
        {
            $key = 'ezsettings.' . ConfigResolver::SCOPE_GLOBAL . '.' . $id;
            $globalValue = $config[$this->baseKey][ConfigResolver::SCOPE_GLOBAL][$id];
            if ( $container->hasParameter( $key ) )
            {
                $globalValue = array_merge(
                    $container->getParameter( $key ),
                    $globalValue
                );
            }
            $container->setParameter( $key, $globalValue );
        }
    }

    /**
     * Registers the internal configuration. For array settings, we merge the
     * arrays defined in scopes default, in the siteaccess groups, in the
     * siteaccess itself and in the global scope. To calculate the precedence
     * of siteaccess group, they are alphabetically sorted.
     *
     * @param string $id id of the setting array to register
     * @param array $config the full configuration as an array
     * @param ContainerBuilder $container
     * @param int $options bit mask of options (@see constants of this class)
     */
    protected function registerInternalConfigArray( $id, array $config, ContainerBuilder $container, $options = 0 )
    {
        $this->registerGlobalConfigArray( $id, $config, $container );
        $defaultSettings = $this->getContainerParameter(
            $container,
            'ezsettings.' . ConfigResolver::SCOPE_DEFAULT . '.' . $id,
            array()
        );
        $globalSettings = $this->getContainerParameter(
            $container,
            'ezsettings.' . ConfigResolver::SCOPE_GLOBAL . '.' . $id,
            array()
        );

        $groupsBySiteaccess = $this->getContainerParameter(
            $container,
            'ezpublish.siteaccess.groups_by_siteaccess',
            array()
        );

        foreach ( $config['siteaccess']['list'] as $sa )
        {
            // for a siteaccess, we have to merge the default value,
            // the group value(s), the siteaccess value and the global
            // value of the settings.
            $groupsSettings = array();
            if ( isset( $groupsBySiteaccess[$sa] ) && is_array( $groupsBySiteaccess[$sa] ) )
            {
                $groupsSettings = $this->groupsArraySetting(
                    $groupsBySiteaccess[$sa], $id,
                    $config, $options & self::MERGE_FROM_SECOND_LEVEL
                );
            }
            $siteaccessSettings = array();
            if ( isset( $config[$this->baseKey][$sa][$id] ) )
            {
                $siteaccessSettings = $config[$this->baseKey][$sa][$id];
            }
            if ( $options & self::MERGE_FROM_SECOND_LEVEL )
            {
                // array_merge() has to be used because we don't
                // know whether we have a hash or a plain array
                $keys1 = array_unique(
                    array_merge(
                        array_keys( $defaultSettings ),
                        array_keys( $groupsSettings ),
                        array_keys( $siteaccessSettings ),
                        array_keys( $globalSettings )
                    )
                );
                $mergedSettings = array();
                foreach ( $keys1 as $key )
                {
                    $mergedSettings[$key] = array_merge(
                        isset( $defaultSettings[$key] ) ? $defaultSettings[$key] : array(),
                        isset( $groupsSettings[$key] ) ? $groupsSettings[$key] : array(),
                        isset( $siteaccessSettings[$key] ) ? $siteaccessSettings[$key] : array(),
                        isset( $globalSettings[$key] ) ? $globalSettings[$key] : array()
                    );
                }
            }
            else
            {
                $mergedSettings = array_merge(
                    $defaultSettings,
                    $groupsSettings,
                    $siteaccessSettings,
                    $globalSettings
                );
            }
            if ( $options & self::UNIQUE )
            {
                $mergedSettings = array_values(
                    array_unique( $mergedSettings )
                );
            }

            $container->setParameter( "ezsettings.$sa.$id", $mergedSettings );
        }
    }
}
