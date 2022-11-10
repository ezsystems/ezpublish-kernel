<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\Limitation;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\User\Role;
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
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation;

final class RoleLimitationType extends AbstractPersistenceLimitationType implements SPILimitationTypeInterface
{
    /**
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function acceptValue(APILimitationValue $limitationValue): void
    {
        if (!$limitationValue instanceof RoleLimitation) {
            throw new InvalidArgumentType(
                '$limitationValue',
                RoleLimitation::class,
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
            try {
                $this->persistence->userHandler()->loadRole($id);
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
        return new RoleLimitation(['limitationValues' => $limitationValues]);
    }

    public function evaluate(APILimitationValue $value, APIUserReference $currentUser, ValueObject $object, array $targets = null)
    {
        if (!$value instanceof RoleLimitation) {
            throw new InvalidArgumentException(
                '$value',
                sprintf('Must be of type: %s', RoleLimitation::class)
            );
        }

        if (
            !$object instanceof Role
            && !$object instanceof UserRoleAssignment
            && !$object instanceof UserGroupRoleAssignment
            && ($targets === null && ($object instanceof User || $object instanceof UserGroup))
        ) {
            return self::ACCESS_ABSTAIN;
        }

        if ($targets !== null) {
            foreach ($targets as $target) {
                if ($target instanceof Role && !$this->evaluateRole($value, $target)) {
                    return self::ACCESS_DENIED;
                }

                return self::ACCESS_GRANTED;
            }
        }

        if ($object instanceof Role) {
            return $this->evaluateRole($value, $object);
        }

        if ($object instanceof UserRoleAssignment || $object instanceof UserGroupRoleAssignment) {
            return $this->evaluateRole($value, $object->getRole());
        }

        return self::ACCESS_DENIED;
    }

    public function getCriterion(APILimitationValue $value, APIUserReference $currentUser)
    {
        throw new NotImplementedException('Role Limitation Criterion');
    }

    public function valueSchema()
    {
        throw new NotImplementedException(__METHOD__);
    }

    private function evaluateRole(RoleLimitation $value, Role $role): bool
    {
        return in_array($role->id, $value->limitationValues);
    }
}
