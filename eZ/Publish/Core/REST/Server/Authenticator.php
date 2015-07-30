<?php

/**
 * File containing the Authenticator base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\REST\Server;

use eZ\Publish\API\Repository\Repository;
use Qafoo\RMF;

/**
 * Authenticator base class.
 */
abstract class Authenticator
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
    abstract public function authenticate(RMF\Request $request);
}
