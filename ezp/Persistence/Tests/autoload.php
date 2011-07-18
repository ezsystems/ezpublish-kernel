<?php

namespace ezp;

if ( ( $fp = @fopen( 'Base/base.php', 'r', true ) ) !== false )
{
    fclose( $fp );
    require_once 'Base/base.php';
}
else
{
    require_once 'Base/src/base.php';
}

spl_autoload_register(
    function ( $class )
    {
        if ( 0 === strpos( $class, '\\' ) )
        {
            $class = substr( $class, 1 );
        }
        if ( 0 === strpos( $class, __NAMESPACE__ ) )
        {
            include __DIR__ . '/../../../' . strtr( $class, '\\', '/' ) . '.php';
        }
    }
);

spl_autoload_register(
    array( 'ezcBase', 'autoload' )
);
