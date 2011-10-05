<?php
/**
 * File containing the DirHandler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io;
use ezp\Base\Configuration,
    ezp\Io\Exception\DirLogic;

/**
 * Handles directory operations
 */
class DirHandler
{
    /**
     * Creates the directory $dir with permissions $perm (if provided).
     * If $recursive is true it will create any missing parent directories, just like 'mkdir -p'.
     * If $dir already exists on the file system, this method will return false.
     *
     * @param string $dir The path of to be created directory
     * @param string $perm Permission, in octal text (i.e. 0777)
     * @param bool $recursive
     * @return bool True in case of success, false otherwise.
     * @throws \ezp\Io\Exception\DirLogic If any problem occurs with mkdir underlying operation
     */
    public static function mkdir( $dir, $perm = false, $recursive = false )
    {
        if ( file_exists( $dir ) )
            return false;

        $dir = DirPath::clean( $dir );
        $perm = $perm ?: static::directoryPermission();

        $oldumask = umask( 0 );
        // Convert every potential warnings/errors into a proper exception
        // This is due to PHP trigerring warnings in mkdir(), unless just returning false.
        // The exception thrown will give the same piece of information, but catchable
        set_error_handler(
            function( $errNo, $errStr )
            {
                throw new DirLogic( $errStr, $errNo );
            }
        );
        $success = mkdir( $dir, $perm, $recursive );
        restore_error_handler();
        umask( $oldumask );

        return $success;
    }

    /**
     * Returns the default permissions to use for directories.
     * The permission is converted from octal text (i.e. 0777) to decimal value.
     *
     * @return int|float
     */
    public static function directoryPermission()
    {
        return octdec( Configuration::getInstance( 'site' )->get( 'FileSettings', 'StorageDirPermissions', '0777' ) );
    }
}
