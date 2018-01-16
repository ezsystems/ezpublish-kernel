<?php

/**
 * File containing the bootstrapping of eZ Publish API for unit test use.
 *
 * Setups class loading.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

// Get global config.php settings
if (!file_exists(__DIR__ . '/config.php')) {
    if (!symlink(__DIR__ . '/config.php-DEVELOPMENT', __DIR__ . '/config.php')) {
        throw new \RuntimeException('Could not symlink config.php-DEVELOPMENT to config.php, please copy config.php-DEVELOPMENT to config.php & customize to your needs!');
    }
}

if (!($settings = include(__DIR__ . '/config.php'))) {
    throw new \RuntimeException('Could not read config.php, please copy config.php-DEVELOPMENT to config.php & customize to your needs!');
}

// Class alias used for BC
// Enables old code which still extends non namespaced TestCase to work
class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');

require_once __DIR__ . '/vendor/autoload.php';
