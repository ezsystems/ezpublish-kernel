<?php

require $sc = __DIR__ . '/../../../../bootstrap.php';

spl_autoload_register( function( $class ) {
    if ( strpos( $class, 'Qafoo' ) === 0 )
    {
        require __DIR__ . '/../../../../library/Qafoo/RMF/src/main/' . str_replace( '\\', '/', $class ) . '.php';
    }
} );

return $sc;
