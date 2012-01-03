<?php
/**
 * File containing the SysInfo class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io;
use ezp\Base\Configuration,
    ezp\Io\DirPath;

/**
 * Utility class for getting useful info on I/O
 */
class SysInfo
{
    /**
     * Returns path of the directory used for storing various kinds of files like cache, temporary files and logs.
     *
     * @return string
     */
    public static function varDirectory()
    {
        return DirPath::clean( Configuration::getInstance( 'site' )->get( 'FileSettings', 'VarDir', 'var' ) );
    }

    /**
     * Returns path of the directory used for storing various kinds of files like images, audio and more.
     * This will include the varDirectory().
     *
     * @return string
     */
    public static function storageDirectory()
    {
        return DirPath::clean(
            array(
                self::varDirectory(),
                Configuration::getInstance( 'site' )->get( 'FileSettings', 'StorageDir', 'storage' )
            )
        );
    }
}
