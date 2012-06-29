<?php
/**
 * File containing the bootstrapping of eZ Publish Next MVC Components
 *
 * Returns instance of Service Container setup with configuration service and setups autoloader.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

$serviceContainer = require __DIR__ . '/../../../bootstrap.php';

require __DIR__ . '/../../../../../../app/autoload.php';

return $serviceContainer;
