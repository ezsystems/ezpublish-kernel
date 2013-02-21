<?php
/**
 * File containing the eZINI mock class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

class eZINI
{
    public static function instance( $file = false )
    {
        return new self( $file );
    }

    public function __construct( $file )
    {
        if ( $file === false )
        {
            $file = 'site.ini';
        }
        $this->file = $file;
    }

    public function variable( $group, $variable )
    {
        if ( !isset( self::$data[ $this->file][$group][$variable] ) )
        {
            throw new Exception( "eZINI setting not found: $this->file / $group / $variable" );
        }

        return self::$data[ $this->file][$group][$variable];
    }

    public function __call( $method, $args )
    {

    }

    public static function __callStatic( $method, $args )
    {

    }

    private static $data = array(
        'site.ini' => array(
            'FileSettings' => array(
                'StorageDirPermissions' => '0777',
                'StorageFilePermissions' => '0666',
            ),
        ),
        'file.ini' => array(
            'FileSettings' => array(
                'Handlers' => array(),
            ),
        ),
    );

    private $file;
}
