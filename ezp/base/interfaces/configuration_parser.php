<?php
/**
 * Configuration Parser Interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

namespace ezp\base;
interface ConfigurationParserInterface
{
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
}

?>