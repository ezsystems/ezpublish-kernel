<?php
/**
 * File containing the DirPath class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io;

/**
 * Utility class to deal with dir paths
 */
class DirPath
{
    /**
     * Cleans a directory path.
     * Removes or add trailing slash in $path if necessary, according to $includeEndSeparator flag.
     *
     * @param string|array $path The directory path. Can also be an array of path.
     *                           If an array of string is provided, then paths will be concatenated
     * @param bool $includeEndSeparator
     * @return string
     */
    public static function clean( $path, $includeEndSeparator = false )
    {
        if ( is_array( $path ) )
            $path = implode( '/', $path );

        $pathLen = strlen( $path );
        $hasEndSeparator = ( $pathLen > 0 && $path[$pathLen - 1] == '/' );
        if ( $includeEndSeparator && !$hasEndSeparator )
            $path .= '/';
        else if ( !$includeEndSeparator && $hasEndSeparator && $pathLen > 1 )
            $path = substr( $path, 0, -1 );

        // Clean up multiple slash occurrences
        if ( strpos( $path, '//' ) !== false )
            $path = preg_replace( '#/{2,}#', '/', $path );

        return $path;
    }
}
