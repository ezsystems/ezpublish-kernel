<?php

/**
 * File containing the Authenticator used for integration tests.
 *
 * ATTENTION: This is a only meant for the test setup for the REST server. DO
 * NOT USE IT IN PRODUCTION!
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server\Authenticator;

use eZ\Publish\Core\REST\Server\Authenticator;
use eZ\Publish\Core\REST\Server\Exceptions\AuthenticationFailedException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Qafoo\RMF;
use InvalidArgumentException;

/**
 * Authenticator for integration tests.
 *
 * This is, by now, just an untested stub.
 *
 * @todo Remove when the REST client is refactored
 */
class BasicAuth extends Authenticator
{
    /**
     * Creates an new Authenticator to $repository.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct(Repository $repository)
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
     * @return bool
     */
    public function authenticate(RMF\Request $request)
    {
        try {
            $this->repository->setCurrentUser(
                $this->repository->getUserService()->loadUserByCredentials(
                    $request->username,
                    $request->password
                )
            );
        } catch (InvalidArgumentException $e) {
            return false;
        } catch (NotFoundException $e) {
            throw new AuthenticationFailedException();
        }
    }
}
