<?php

namespace ezp;

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
        else
        {
            throw new RuntimeException( "Class not found: $class" );
        }
    }
);
