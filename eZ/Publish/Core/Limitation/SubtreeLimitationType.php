<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Limitation;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation as APISubtreeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\SPI\Limitation\Target\Version;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use eZ\Publish\Core\Repository\Values\Content\Query\Criterion\PermissionSubtree;

/**
 * SubtreeLimitation is a Content Limitation & a Role Limitation.
 */
class SubtreeLimitationType extends AbstractPersistenceLimitationType implements SPILimitationTypeInterface
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
        if (!$limitationValue instanceof APISubtreeLimitation) {
            throw new InvalidArgumentType('$limitationValue', 'APISubtreeLimitation', $limitationValue);
        } elseif (!is_array($limitationValue->limitationValues)) {
            throw new InvalidArgumentType('$limitationValue->limitationValues', 'array', $limitationValue->limitationValues);
        }

        foreach ($limitationValue->limitationValues as $key => $path) {
            if (!is_string($path)) {
                throw new InvalidArgumentType("\$limitationValue->limitationValues[{$key}]", 'string', $path);
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
        foreach ($limitationValue->limitationValues as $key => $path) {
            try {
                $pathArray = explode('/', trim($path, '/'));
                $subtreeRootLocationId = end($pathArray);
                $spiLocation = $this->persistence->locationHandler()->load($subtreeRootLocationId);
            } catch (APINotFoundException $e) {
            }

            if (!isset($spiLocation) || strpos($spiLocation->pathString, $path) !== 0) {
                $validationErrors[] = new ValidationError(
                    "limitationValues[%key%] => '%value%' does not exist in the backend",
                    null,
                    [
                        'value' => $path,
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
        return new APISubtreeLimitation(['limitationValues' => $limitationValues]);
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
        $targets = $targets ?? [];

        if (!$value instanceof APISubtreeLimitation) {
            throw new InvalidArgumentException('$value', 'Must be of type: APISubtreeLimitation');
        }

        if ($object instanceof ContentCreateStruct) {
            return $this->evaluateForContentCreateStruct($value, $targets);
        } elseif ($object instanceof Content) {
            $object = $object->getVersionInfo()->getContentInfo();
        } elseif ($object instanceof VersionInfo) {
            $object = $object->getContentInfo();
        } elseif (!$object instanceof ContentInfo) {
            // As this is Role limitation we need to signal abstain on unsupported $object
            return self::ACCESS_ABSTAIN;
        }

        $targets = array_filter($targets, function ($target) {
            return !$target instanceof Version;
        });

        // Load locations if no specific placement was provided
        if (empty($targets)) {
            // Skip check if content is in trash and no location is provided to check against
            if ($object->isTrashed()) {
                return self::ACCESS_ABSTAIN;
            }

            if ($object->isPublished()) {
                $targets = $this->persistence->locationHandler()->loadLocationsByContent($object->id);
            } else {
                // @todo Need support for draft locations to work correctly
                $targets = $this->persistence->locationHandler()->loadParentLocationsForDraftContent($object->id);
            }
        }

        foreach ($targets as $target) {
            if (!$target instanceof Location && !$target instanceof SPILocation) {
                // As this is Role limitation we need to signal abstain on unsupported $targets
                return self::ACCESS_ABSTAIN;
            }

            foreach ($value->limitationValues as $limitationPathString) {
                if ($target->pathString === $limitationPathString) {
                    return true;
                }
                if (strpos($target->pathString, $limitationPathString) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Evaluate permissions for ContentCreateStruct against LocationCreateStruct placements.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $targets does not contain
     *         objects of type LocationCreateStruct
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param array $targets
     *
     * @return bool
     */
    protected function evaluateForContentCreateStruct(APILimitationValue $value, array $targets)
    {
        // If targets is empty/null return false as user does not have access
        // to content w/o location with this limitation
        if (empty($targets)) {
            return false;
        }

        $hasLocationCreateStruct = false;
        foreach ($targets as $target) {
            if (!$target instanceof LocationCreateStruct) {
                continue;
            }

            $hasLocationCreateStruct = true;
            $target = $this->persistence->locationHandler()->load($target->parentLocationId);

            // For ContentCreateStruct all placements must match
            foreach ($value->limitationValues as $limitationPathString) {
                if ($target->pathString === $limitationPathString) {
                    continue 2;
                }
                if (strpos($target->pathString, $limitationPathString) === 0) {
                    continue 2;
                }
            }

            return false;
        }

        if (false === $hasLocationCreateStruct) {
            throw new InvalidArgumentException(
                '$targets',
                'If $object is ContentCreateStruct must contain objects of type: LocationCreateStruct'
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
        if (empty($value->limitationValues)) {
            // no limitation values
            throw new \RuntimeException('$value->limitationValues is empty, it should not have been stored in the first place');
        }

        if (!isset($value->limitationValues[1])) {
            // 1 limitation value: EQ operation
            return new PermissionSubtree($value->limitationValues[0]);
        }

        // several limitation values: IN operation
        return new PermissionSubtree($value->limitationValues);
    }

    /**
     * Returns info on valid $limitationValues.
     *
     * @return mixed[]|int In case of array, a hash with key as valid limitations value and value as human readable name
     *                     of that option, in case of int on of VALUE_SCHEMA_ constants.
     */
    public function valueSchema()
    {
        return self::VALUE_SCHEMA_LOCATION_PATH;
    }
}
