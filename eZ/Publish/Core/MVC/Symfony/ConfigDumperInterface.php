<?php
/**
 * File containing the ConfigDumperInterface interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony;

/**
 * Interface for configuration dumpers.
 * Use this interface when you want to dump settings in a configuration file for instance.
 */
interface ConfigDumperInterface
{
    const OPT_DEFAULT = 0,
          OPT_BACKUP_CONFIG = 1;

    /**
     * Dumps settings contained in $configArray in a configuration storage (e.g. a YAML config file).
     *
     * @param array $configArray Hash of settings.
     * @param int $options A binary combination of options. See class OPT_* class constants in {@link \eZ\Publish\Core\MVC\Symfony\ConfigDumperInterface}
     *
     * @return void
     */
    public function dump( array $configArray, $options = ConfigDumperInterface::OPT_DEFAULT );
}
