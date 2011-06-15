<?php
/**
 * Configuration Parser Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

namespace ezp\base;
interface Interface_Configuration_Parser
{
    /**
     * Construct an instance for a specific file
     *
     * @param string $file A valid file name, file must exist by the time you call parse()!
     *        For writer {@see Interface_Configuration_Writer}, file will be overwritten if it exists!
     */
    public function __construct( $file );

    /**
     * Parse file and return raw configuration data
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
     */
    public function parse();
}

?>