<?php
/**
 * Configuration Parser Interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

namespace ezp\Base\Interfaces;

/**
 * Configuration Interfaces for configuration parser & writer
 *
 * @package ezp
 * @subpackage base
 */
interface ConfigurationParser
{
    /**
     * Construct an instance for a specific file
     *
     * @param string $file A valid file name, will be overwritten if it exists by {@link write()}
     */
    public function __construct( $file );

    /**
     * Parse file and return raw configuration data
     *
     * @param string $fileContent
     * @return array A plain array structure of configuration data where array clearing
     *         is marked with {@link Configuration::TEMP_INI_UNSET_VAR} and php variables are plain
     *         php values(numbers, floats, true and false). In addition strings are rtrimmed to
     *         avoid common user mistakes when dealing with configuration data (trailing whitespace).
     *         eg (ini example):
     *             [section]
     *             list[]
     *             list[]=item
     *             list[]=false
     *
     *             var=true
     *             num=2
     *             float=1.2
     *             string=1,5 
     *
     *         Result:
     *             array(
     *                 'section' => array(
     *                     'list' => array(
     *                         '__UNSET__',
     *                         'item',
     *                         false,
     *                     ),
     *                     'var' => true,
     *                     'num' => 2,
     *                     'float' => 1.2,
     *                     'string' => '1,5',
     *                 )
     *             )
     */
    public function parse( $fileContent );

    /**
     * Store raw configuration data to file
     *
     * @see parse() For $configurationData definition
     * @param array $configurationData
     */
    public function write( array $configurationData );
}

?>