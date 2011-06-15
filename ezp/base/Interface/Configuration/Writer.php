<?php
/**
 * Configuration Writer Interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage base
 */

namespace ezp\base;
interface Interface_Configuration_Writer extends Interface_Configuration_Parser
{
    /**
     * Store raw configuration data to file
     *
     * @see Interface_Configuration_Parser::parse() For $configurationData definition
     * @param array $configurationData
     */
    public function write( array $configurationData );
}

?>