<?php

/**
 * File containing the UserInterface class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security;

use eZ\Publish\API\Repository\Values\User\User as APIUser;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

/**
 * Interface for Repository based users.
 */
interface UserInterface extends BaseUserInterface
{
    /**
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getAPIUser();

    /**
     * @deprecated Will be replaced by {@link ReferenceUserInterface::getAPIUser()}, adding LogicException to signature.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser
     */
    public function setAPIUser(APIUser $apiUser);
}
