<?php
/**
 * File containing the AbstractParser parser.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser,
    Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Provides helpers to deal with settings which are array
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
     * Returns the value under the $id in the $container. if the container does
     * not known this $id, returns $default
     *
     * @param ContainerBuilder $container
     * @param string $id
     * @param mixed $default
     * @return mixed
     */
    private function getContainerParameter( ContainerBuilder $container, $id, $default = null )
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
     * @param string $id id of the setting array under ezpublish.system.<group_name>
     * @param array $config the full configuration array
     * @param bool $mergeFromSecondLevel if false, array_merge is used to merge
     *             the arrays from different group, otherwise the merge is done
     *             from the second level.
     * @return array
     */
    private function groupsArraySetting( array $groups, $id, array $config, $mergeFromSecondLevel = false )
    {
        $groupsSettings = array();
        sort( $groups );
        foreach ( $groups as $group )
        {
            if ( isset( $config['system'][$group][$id] ) )
            {
                if ( $mergeFromSecondLevel )
                {
                    $keys = array_keys( $config['system'][$group][$id] );
                    foreach ( $keys as $key )
                    {
                        if ( !isset( $groupsSettings[$key] ) )
                        {
                            $groupsSettings[$key] = $config['system'][$group][$id][$key];
                        }
                        else
                        {
                            $groupsSettings[$key] = array_merge(
                                $groupsSettings[$key],
                                $config['system'][$group][$id][$key]
                            );
                        }
                    }
                }
                else
                {
                    $groupsSettings = array_merge(
                        $groupsSettings,
                        $config['system'][$group][$id]
                    );
                }
            }
        }
        return $groupsSettings;
    }

    /**
     * Registers the internal configuration. For settings arrays, we merge the
     * arrays defined in scopes default, in the siteaccess groups, in the
     * siteaccess itself and in the global scope. Too calculate the precedence
     * of siteaccess group, they are sorted by name.
     *
     * @param string $id id of the setting array to register
     * @param array $config the full configuration as an array
     * @param ContainerBuilder $container
     * @param int $options bit mask of options (@see constants of this class)
     */
    protected function registerInternalConfigArray( $id, array $config, ContainerBuilder $container, $options = 0 )
    {
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
            if ( isset( $config['system'][$sa][$id] ) )
            {
                $siteaccessSettings = $config['system'][$sa][$id];
            }
            if ( $options & self::MERGE_FROM_SECOND_LEVEL )
            {
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

?>
