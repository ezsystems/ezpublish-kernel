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
interface ConfigurationWriterInterface extends ConfigurationParserInterface
{
    /**
     * Store raw configuration data to file
     *
     * @see ConfigurationParserInterface::parse() For $configurationData definition
     * @param array $configurationData
     */
    public function write( array $configurationData );
}

?>