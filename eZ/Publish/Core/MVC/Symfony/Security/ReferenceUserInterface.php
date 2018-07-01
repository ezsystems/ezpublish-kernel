<?php

/**
 * File containing the ReferenceUserInterface class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security;

/**
 * Interface for Repository based users, where we only serialize user id / Reference in session values.
 *
 * Use of user reference allows us to strip api user on serialization to avoid it being sent to session storage,
 * as UserProvider calls {@link UserInterface::setAPIUser()} during refresh stage.
 *
 * This method and logic implied above will be added to UserInterface in 7.0, where this interface will be deprecated,
 * so for forward compatibility make sure to also implement the method, even if you don't implement this interface.
 */
interface ReferenceUserInterface extends UserInterface
{
    /**
     * @return \eZ\Publish\API\Repository\Values\User\UserReference
     */
    public function getAPIUserReference();

    /**
     * @throws \LogicException If api user has not been refreshed yet by UserProvider after being
     *         unserialized from session.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getAPIUser();
}
