<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\Limitation;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\UserGroupRoleAssignment;
use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\User\UserRoleAssignment;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\Limitation\AbstractPersistenceLimitationType;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\MemberOfLimitation;

final class MemberOfLimitationType extends AbstractPersistenceLimitationType implements SPILimitationTypeInterface
{
    public const SELF_USER_GROUP = -1;

    /**
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function acceptValue(APILimitationValue $limitationValue): void
    {
        if (!$limitationValue instanceof MemberOfLimitation) {
            throw new InvalidArgumentType(
                '$limitationValue',
                MemberOfLimitation::class,
                $limitationValue
            );
        }

        if (!is_array($limitationValue->limitationValues)) {
            throw new InvalidArgumentType(
                '$limitationValue->limitationValues',
                'array',
                $limitationValue->limitationValues
            );
        }

        foreach ($limitationValue->limitationValues as $key => $id) {
            if (!is_int($id)) {
                throw new InvalidArgumentType("\$limitationValue->limitationValues[{$key}]", 'int|string', $id);
            }
        }
    }

    public function validate(APILimitationValue $limitationValue)
    {
        $validationErrors = [];

        foreach ($limitationValue->limitationValues as $key => $id) {
            if ($id === self::SELF_USER_GROUP) {
                continue;
            }
            try {
                $this->persistence->contentHandler()->loadContentInfo($id);
            } catch (NotFoundException $e) {
                $validationErrors[] = new ValidationError(
                    "limitationValues[%key%] => '%value%' does not exist in the backend",
                    null,
                    [
                        'value' => $id,
                        'key' => $key,
                    ]
                );
            }
        }

        return $validationErrors;
    }

    /**
     * @param mixed[] $limitationValues
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    public function buildValue(array $limitationValues): APILimitationValue
    {
        return new MemberOfLimitation(['limitationValues' => $limitationValues]);
    }

    public function evaluate(APILimitationValue $value, APIUserReference $currentUser, ValueObject $object, array $targets = null)
    {
        if (!$value instanceof MemberOfLimitation) {
            throw new InvalidArgumentException(
                '$value',
                sprintf('Must be of type: %s', MemberOfLimitation::class)
            );
        }

        if (!$object instanceof User
            && !$object instanceof UserGroup
            && !$object instanceof UserRoleAssignment
            && !$object instanceof UserGroupRoleAssignment
        ) {
            return self::ACCESS_ABSTAIN;
        }

        if ($object instanceof User) {
            return $this->evaluateUser($value, $object, $currentUser);
        }

        if ($object instanceof UserGroup) {
            return $this->evaluateUserGroup($value, $object, $currentUser);
        }

        if ($object instanceof UserRoleAssignment) {
            return $this->evaluateUser($value, $object->getUser(), $currentUser);
        }

        if ($object instanceof UserGroupRoleAssignment) {
            return $this->evaluateUserGroup($value, $object->getUserGroup(), $currentUser);
        }

        return self::ACCESS_DENIED;
    }

    public function getCriterion(APILimitationValue $value, APIUserReference $currentUser)
    {
        throw new NotImplementedException('Member of Limitation Criterion');
    }

    public function valueSchema()
    {
        throw new NotImplementedException(__METHOD__);
    }

    private function evaluateUser(MemberOfLimitation $value, User $object, APIUserReference $currentUser): bool
    {
        if (empty($value->limitationValues)) {
            return self::ACCESS_DENIED;
        }

        $userLocations = $this->persistence->locationHandler()->loadLocationsByContent($object->getUserId());

        $userGroups = [];
        foreach ($userLocations as $userLocation) {
            $userGroups[] = $this->persistence->locationHandler()->load($userLocation->parentId);
        }
        $userGroupsIdList = array_column($userGroups, 'contentId');
        $limitationValuesUserGroupsIdList = $value->limitationValues;

        if (in_array(self::SELF_USER_GROUP, $limitationValuesUserGroupsIdList)) {
            $currentUserGroupsIdList = $this->getCurrentUserGroupsIdList($currentUser);

            // Granted, if current user is in exactly those same groups
            if (count(array_intersect($userGroupsIdList, $currentUserGroupsIdList)) === count($userGroupsIdList)) {
                return self::ACCESS_GRANTED;
            }

            // Unset SELF value, for next check
            $key = array_search(self::SELF_USER_GROUP, $limitationValuesUserGroupsIdList);
            unset($limitationValuesUserGroupsIdList[$key]);
        }

        // Granted, if limitationValues matched user groups 1:1
        if (!empty($limitationValuesUserGroupsIdList)
            && empty(array_diff($userGroupsIdList, $limitationValuesUserGroupsIdList))
        ) {
            return self::ACCESS_GRANTED;
        }

        return self::ACCESS_DENIED;
    }

    private function evaluateUserGroup(MemberOfLimitation $value, UserGroup $userGroup, APIUserReference $currentUser): bool
    {
        $limitationValuesUserGroupsIdList = $value->limitationValues;
        if (in_array(self::SELF_USER_GROUP, $limitationValuesUserGroupsIdList)) {
            $limitationValuesUserGroupsIdList = $this->getCurrentUserGroupsIdList($currentUser);
        }

        return in_array($userGroup->id, $limitationValuesUserGroupsIdList);
    }

    private function getCurrentUserGroupsIdList(APIUserReference $currentUser): array
    {
        $currentUserLocations = $this->persistence->locationHandler()->loadLocationsByContent($currentUser->getUserId());
        $currentUserGroups = [];
        foreach ($currentUserLocations as $currentUserLocation) {
            $currentUserGroups[] = $this->persistence->locationHandler()->load($currentUserLocation->parentId);
        }

        return array_column(
            $currentUserGroups,
            'contentId'
        );
    }
}
