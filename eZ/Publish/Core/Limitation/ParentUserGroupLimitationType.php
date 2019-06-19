<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Limitation;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation as APIParentUserGroupLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\SPI\Limitation\Target;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;

/**
 * ParentUserGroupLimitation is a Content limitation.
 */
class ParentUserGroupLimitationType extends AbstractPersistenceLimitationType implements SPILimitationTypeInterface
{
    /**
     * Accepts a Limitation value and checks for structural validity.
     *
     * Makes sure LimitationValue object and ->limitationValues is of correct type.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected type/structure
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitationValue
     */
    public function acceptValue(APILimitationValue $limitationValue)
    {
        if (!$limitationValue instanceof APIParentUserGroupLimitation) {
            throw new InvalidArgumentType('$limitationValue', 'APIParentUserGroupLimitation', $limitationValue);
        } elseif (!is_array($limitationValue->limitationValues)) {
            throw new InvalidArgumentType('$limitationValue->limitationValues', 'array', $limitationValue->limitationValues);
        }

        foreach ($limitationValue->limitationValues as $key => $value) {
            // Accept a true value for b/c with 5.0
            if ($value === true) {
                $limitationValue->limitationValues[$key] = 1;
            } elseif (is_string($value) && ctype_digit($value)) {
                // Cast integers passed as string to int
                $limitationValue->limitationValues[$key] = (int)$value;
            } elseif (!is_int($value)) {
                throw new InvalidArgumentType("\$limitationValue->limitationValues[{$key}]", 'int', $value);
            }
        }
    }

    /**
     * Makes sure LimitationValue->limitationValues is valid according to valueSchema().
     *
     * Make sure {@link acceptValue()} is checked first!
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitationValue
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(APILimitationValue $limitationValue)
    {
        $validationErrors = [];
        foreach ($limitationValue->limitationValues as $key => $value) {
            if ($value !== 1) {
                $validationErrors[] = new ValidationError(
                    "limitationValues[%key%] => '%value%' must be 1 (owner)",
                    null,
                    [
                        'value' => $value,
                        'key' => $key,
                    ]
                );
            }
        }

        return $validationErrors;
    }

    /**
     * Create the Limitation Value.
     *
     * @param mixed[] $limitationValues
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    public function buildValue(array $limitationValues)
    {
        return new APIParentUserGroupLimitation(['limitationValues' => $limitationValues]);
    }

    /**
     * Evaluate permission against content & target(placement/parent/assignment).
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If any of the arguments are invalid
     *         Example: If LimitationValue is instance of ContentTypeLimitationValue, and Type is SectionLimitationType.
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If value of the LimitationValue is unsupported
     *         Example if OwnerLimitationValue->limitationValues[0] is not one of: [ 1,  2 ]
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $currentUser
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\ValueObject[]|null $targets The context of the $object, like Location of Content, if null none where provided by caller
     *
     * @return bool
     */
    public function evaluate(APILimitationValue $value, APIUserReference $currentUser, ValueObject $object, array $targets = null)
    {
        if (!$value instanceof APIParentUserGroupLimitation) {
            throw new InvalidArgumentException('$value', 'Must be of type: APIParentUserGroupLimitation');
        }

        if ($value->limitationValues[0] != 1) {
            throw new BadStateException(
                'Parent User Group limitation',
                'expected limitation value to be 1 but got:' . $value->limitationValues[0]
            );
        }

        // Parent Limitations are usually used by content/create where target is specified, so we return false if not provided.
        if (empty($targets)) {
            return false;
        }

        $locationHandler = $this->persistence->locationHandler();
        $currentUserLocations = $locationHandler->loadLocationsByContent($currentUser->getUserId());
        if (empty($currentUserLocations)) {
            return false;
        }

        $hasMandatoryTarget = false;
        foreach ($targets as $target) {
            if ($target instanceof LocationCreateStruct) {
                $hasMandatoryTarget = true;
                $target = $locationHandler->load($target->parentLocationId);
            }

            if ($target instanceof Location) {
                $hasMandatoryTarget = true;
                // $target is assumed to be parent in this case
                $parentOwnerId = $target->getContentInfo()->ownerId;
            } elseif ($target instanceof SPILocation) {
                $hasMandatoryTarget = true;
                // $target is assumed to be parent in this case
                $spiContentInfo = $this->persistence->contentHandler()->loadContentInfo($target->contentId);
                $parentOwnerId = $spiContentInfo->ownerId;
            } else {
                continue;
            }

            if ($parentOwnerId === $currentUser->getUserId()) {
                continue;
            }

            /*
             * As long as SPI userHandler and API UserService does not speak the same language, this is the ugly truth;
             */
            $locationHandler = $this->persistence->locationHandler();
            $parentOwnerLocations = $locationHandler->loadLocationsByContent($parentOwnerId);
            if (empty($parentOwnerLocations)) {
                return false;
            }

            foreach ($parentOwnerLocations as $parentOwnerLocation) {
                foreach ($currentUserLocations as $currentUserLocation) {
                    if ($parentOwnerLocation->parentId === $currentUserLocation->parentId) {
                        continue 3;
                    }
                }
            }

            return false;
        }

        if (false === $hasMandatoryTarget) {
            throw new InvalidArgumentException(
                '$targets',
                'Must contain objects of type: Location or LocationCreateStruct'
            );
        }

        return true;
    }

    /**
     * Returns Criterion for use in find() query.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $currentUser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface
     */
    public function getCriterion(APILimitationValue $value, APIUserReference $currentUser)
    {
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException(__METHOD__);
    }

    /**
     * Returns info on valid $limitationValues.
     *
     * @return mixed[]|int In case of array, a hash with key as valid limitations value and value as human readable name
     *                     of that option, in case of int on of VALUE_SCHEMA_ constants.
     */
    public function valueSchema()
    {
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException(__METHOD__);
    }
}
