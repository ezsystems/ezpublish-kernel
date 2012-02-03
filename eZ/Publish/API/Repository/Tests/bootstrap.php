<?php
namespace eZ\Publish;

define( 'EZ_PUBLISH_API_TEST_ROOT', realpath( __DIR__ . '/../../../../../' ) . '/' );

spl_autoload_register( function( $class )
{
    if ( 0 !== strpos( $class, __NAMESPACE__ ) )
    {
        return;
    }

    $file = EZ_PUBLISH_API_TEST_ROOT . strtr( $class, '\\', '/' ) . '.php';
    if ( file_exists( $file ) )
    {
        include $file;
    }
} );
