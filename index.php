<?php
/**
 * ezp prototype
 *
 * This file on purpose does not use any PHP 5 language features to be able to exit with
 * message about wrong php version even on PHP 4.
 *
 * @copyright Copyright (C) 2012 andrerom & eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 *
 * @doc Copy this file to www/index.php to get started, for more info see doc/readme.txt
 */


// Make sure we are on php 5.3 and validate request_order if not in cli mode
if ( version_compare( PHP_VERSION, '5.3' ) < 0 )
{
    echo '<h1>ezp does not like your PHP version: ' . PHP_VERSION . '</h1>';
    echo '<p>ezp requires PHP 5.3 and higher!</p>';
    exit;
}
else if ( PHP_SAPI !== 'cli' && ini_get( 'request_order' ) !== 'GP' )
{
    echo '<h1>ezp does not like your <a href="http://no2.php.net/manual/en/ini.core.php#ini.request-order">request_order</a> value: ' . ini_get('request_order'). '</h1>';
    echo '<p>Only \'GP\' is supported due to security concerns!</p>';
    exit;
}


ob_start();

// Used by bootstrap for request object
$indexFile = __FILE__;

/**
 * Get ServiceContainer
 * @var ezp\Base\ServiceContainer $serviceContainer
 */
$serviceContainer = require 'bootstrap.php';

// Ignore user abort now that we are about to run controller
ignore_user_abort( true );

// Ask for result, this will execute controller based on request object setup in bootstrap.php
$sectionService = $serviceContainer->getRepository()->getSectionService();
$section = $sectionService->loadSection( 1 )
echo $section->name;



/* @todo: Should be moved to a debug class using shutdown event
echo "\n<br /><small>Page Generated in: " . ( microtime(true) - $request->microTime ) * 1000 . " microseconds";
echo ', memory usage: ' . number_format( memory_get_usage() / 1024, 4 ) . ', peak: ' . number_format( memory_get_peak_usage() / 1024, 4 );
echo ', debug: '. ( ezp\system\Configuration::getInstance()->get( 'autoload', 'development-mode' ) ? 'On' : 'Off' );
echo '</small>';*/
