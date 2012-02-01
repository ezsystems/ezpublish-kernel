<?php
/**
 * eZ Publish 5.x
 *
 * This file on purpose does not use any PHP 5 language features to be able to exit with
 * message about wrong php version even on PHP 4.
 *
 * @copyright Copyright (C) 2012 andrerom & eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package eZ
 *
 * @doc Copy this file to www/index.php to get started, for more info see doc/readme.txt
 */


// Make sure we are on php 5.3 and validate request_order if not in cli mode
if ( version_compare( PHP_VERSION, '5.3' ) < 0 )
{
    echo '<h1>eZ Publish does not like your PHP version: ' . PHP_VERSION . '</h1>';
    echo '<p>PHP 5.3.0 or higher is required!</p>';
    exit;
}
else if ( PHP_SAPI !== 'cli' && ini_get( 'request_order' ) !== 'GP' )
{
    echo '<h1>eZ Publish does not like your <a href="http://no2.php.net/manual/en/ini.core.php#ini.request-order">request_order</a> value: ' . ini_get('request_order'). '</h1>';
    echo '<p>Only \'GP\' is supported due to security concerns!</p>';
    exit;
}


ob_start();

// Used by bootstrap for request object
$indexFile = __FILE__;

/**
 * Get ServiceContainer
 * @var \eZ\Publish\Core\Base\ServiceContainer $serviceContainer
 */
$serviceContainer = require 'bootstrap.php';

// Ignore user abort now that we are about to run controller
ignore_user_abort( true );

// Ask for result, this will execute controller based on request object setup in bootstrap.php
$sectionService = $serviceContainer->getRepository()->getSectionService();
$section = $sectionService->loadSection( 1 );
echo $section->name;

$sectionUpdateStruct = new eZ\Publish\API\Values\Content\SectionUpdateStruct();
$sectionUpdateStruct->name = 'Standard 2';
$sectionUpdateStruct->identifier = 'standard_2';

$section = $sectionService->updateSection( $section, $sectionUpdateStruct );
echo $section->name;
echo $section->identifier;
