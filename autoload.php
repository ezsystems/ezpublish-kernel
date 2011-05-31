<?php
/**
 * simple PSR-0 like autoload function
 * Note: Avoid underscode in namespaces, this code threats them as DIRECTORY_SEPARATOR
 */

function psrAutoload( $className )
{
    if ( strpos( $className, 'ezx\\' ) !== 0 && strpos( $className, 'ezp\\' ) !== 0 )
        return false;

    $fileName = './' . str_replace( array('\\', '_'), DIRECTORY_SEPARATOR, $className ) . '.php';
    return require( $fileName );
}

spl_autoload_register( 'psrAutoload' );
