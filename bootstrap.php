<?php
/**
 * File containing the bootstrapping of eZ Publish API
 *
 * Setups class loading.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


// Get globl config.php settings
if ( !( $classLoader = include './vendor/autoload.php' ) )
{
    throw new \RuntimeException(
        'Could not find composer autoloader, make sure you have installed composer and running from project root!' .
        ( strpos( __DIR__, 'vendor/ezsystems/' ) ? "\n in case of eZ Publish 5, from it's root & not ezpublish-api root" : '' )
    );
}

return include 'container.php';
