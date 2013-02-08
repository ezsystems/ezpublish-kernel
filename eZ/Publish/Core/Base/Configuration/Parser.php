<?php
/**
 * Configuration Parser Interface
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Configuration;

/**
 * Configuration Interfaces for configuration parser & writer
 */
interface Parser
{
    /**
     * Construct an instance of Parser
     *
     * @param array $settings
     */
    public function __construct( array $settings );

    /**
     * Parse file and return raw configuration data
     *
     * @param string $fileName A valid file name
     * @param string $fileContent
     *
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
     *
     * @todo Define exceptions
     */
    public function parse( $fileName, $fileContent );

    /**
     * Store raw configuration data to file
     *
     * @see parse() For $configurationData definition
     * @param string $fileName A valid file name, will be overwritten if it exists
     * @param array $configurationData
     *
     * @todo Define exceptions
     */
    public function write( $fileName, array $configurationData );
}
