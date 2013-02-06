<?php
/**
 * File containing the Authenticator used for integration tests
 *
 * ATTENTION: This is a only meant for the test setup for the REST server. DO
 * NOT USE IT IN PRODUCTION!
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Authenticator;

use eZ\Publish\Core\REST\Server\Authenticator;
use eZ\Publish\API\Repository\Repository;

use Qafoo\RMF;

/**
 * Authenticator for integration tests
 */
class IntegrationTest extends Authenticator
{
    /**
     * Creates an new Authenticator to $repository
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct( Repository $repository )
    {
        $this->repository = $repository;
    }

    /**
     * Authenticates the user based on the given request.
     *
     * Performs an authentication based on the given $request and sets the
     * authenticated user into the $repository. Returns true on success, false
     * of authentication was not possible or did not succeed.
     *
     * @param RMF\Request $request
     *
     * @return boolean
     */
    public function authenticate( RMF\Request $request )
    {
        try
        {
            $this->repository->setCurrentUser(
                $this->repository->getUserService()->loadUser( $request->testUser )
            );
        }
        catch ( \InvalidArgumentException $e )
        {
            throw new \RuntimeException( "The Integration Test Authenticator requires a test user ID to be set using the HTTP Header X-Test-User." );
        }
    }
}
