<?php
/**
 * File containing the BaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub;

$repository = new RepositoryStub(
    __DIR__ . '/Fixtures',
    ( isset( $_ENV['backendVersion'] ) ? (int) $_ENV['backendVersion'] : 5 )
);

return $repository;