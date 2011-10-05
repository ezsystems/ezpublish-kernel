<?php
/**
 * File containing the FileHandler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io;
use ezp\Base\Configuration,
    ezp\Io\Exception\FileLogic;

/**
 * Description of FileHandler
 */
class FileHandler
{
    /**
     * Copies $sourceFilename to $destinationFilename
     *
     * @param string $sourceFilename
     * @param string $destinationFilename
     * @return bool True in case of success, false otherwise
     * @throws \ezp\Io\Exception\FileLogic If any problem occurs with mkdir underlying operation
     */
    public static function copy( $sourceFilename, $destinationFilename )
    {
        // Convert every potential warnings/errors into a proper exception
        // This is due to PHP trigerring warnings in copy(), unless just returning false.
        // The exception thrown will give the same piece of information, but catchable
        set_error_handler(
            function( $errNo, $errStr )
            {
                throw new FileLogic( $errStr, $errNo );
            }
        );
        $success = copy( $sourceFilename, $destinationFilename );
        chmod( $destinationFilename, static::filePermission() );
        restore_error_handler();

        return $success;
    }

    /**
     * Returns the default permissions to use for files.
     * The permission is converted from octal text (i.e. 0777) to decimal value.
     *
     * @return int|float
     */
    public static function filePermission()
    {
        return octdec( Configuration::getInstance( 'site' )->get( 'FileSettings', 'StorageFilePermissions', '0666' ) );
    }
}
