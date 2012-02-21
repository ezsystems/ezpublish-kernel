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
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\UserStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\User\PolicyStub;

// TODO: REMOVE THIS WORKAROUND AND CREATE A FRESH USER
$user   = new UserStub( array( 'id' => 1 ) );
$policy = new PolicyStub( array( 'module' => '*', 'function' => '*' ) );

$repository = new RepositoryStub( __DIR__ . '/Fixtures/full_dump.php' );
$repository->setCurrentUser( $user );
// TODO: REMOVE THIS WORKAROUND AND CREATE POLICIES
$repository->getRoleService()->setPoliciesForUser( $user, array( $policy ) );

return $repository;