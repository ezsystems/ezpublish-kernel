<?php

/**
 * File containing the RoleId identify definer class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\User\ContextProvider;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Repository\Values\User\UserRoleAssignment;
use FOS\HttpCache\UserContext\ContextProviderInterface;
use FOS\HttpCache\UserContext\UserContext;

/**
 * Identity definer based on current user role ids and role limitations.
 */
class RoleContextProvider implements ContextProviderInterface
{
    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    protected $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function updateUserContext(UserContext $context)
    {
        $user = $this->repository->getCurrentUser();
        /** @var \eZ\Publish\API\Repository\Values\User\RoleAssignment[] $roleAssignments */
        $roleAssignments = $this->repository->sudo(
            function (Repository $repository) use ($user) {
                return $repository->getRoleService()->getRoleAssignmentsForUser($user, true);
            }
        );

        $roleIds = array();
        $limitationValues = array();
        /** @var UserRoleAssignment $roleAssignment */
        foreach ($roleAssignments as $roleAssignment) {
            $roleId = $roleAssignment->getRole()->id;
            $roleIds[] = $roleId;
            $limitation = $roleAssignment->getRoleLimitation();
            // If a limitation is present, store the limitation values by roleId
            if ($limitation !== null) {
                $limitationValuesKey = sprintf('%s-%s', $roleId, $limitation->getIdentifier());
                $limitationValues[$limitationValuesKey] = array();
                foreach ($limitation->limitationValues as $value) {
                    $limitationValues[$limitationValuesKey][] = $value;
                }
            }
        }

        $context->addParameter('roleIdList', $roleIds);
        $context->addParameter('roleLimitationList', $limitationValues);
    }
}
