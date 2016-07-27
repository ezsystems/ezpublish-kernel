<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Limitation;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation as APIParentDepthLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;

/**
 * ParentDepthLimitation is a Content limitation.
 */
class ParentDepthLimitationType extends AbstractPersistenceLimitationType implements SPILimitationTypeInterface
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
        if (!$limitationValue instanceof APIParentDepthLimitation) {
            throw new InvalidArgumentType('$limitationValue', 'APIParentDepthLimitation', $limitationValue);
        } elseif (!is_array($limitationValue->limitationValues)) {
            throw new InvalidArgumentType('$limitationValue->limitationValues', 'array', $limitationValue->limitationValues);
        }

        foreach ($limitationValue->limitationValues as $key => $value) {
            // Cast integers passed as string to int
            if (is_string($value) && ctype_digit($value)) {
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
        $validationErrors = array();

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
        return new APIParentDepthLimitation(array('limitationValues' => $limitationValues));
    }

    /**
     * Evaluate permission against content & target(placement/parent/assignment).
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If any of the arguments are invalid
     *         Example: If LimitationValue is instance of ContentTypeLimitationValue, and Type is SectionLimitationType.
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If value of the LimitationValue is unsupported
     *         Example if OwnerLimitationValue->limitationValues[0] is not one of: [ 1,  2 ]
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
        if (!$value instanceof APIParentDepthLimitation) {
            throw new InvalidArgumentException('$value', 'Must be of type: APIParentDepthLimitation');
        }

        if ($object instanceof ContentCreateStruct) {
            return $this->evaluateForContentCreateStruct($value, $targets);
        } elseif ($object instanceof Content) {
            $object = $object->getVersionInfo()->getContentInfo();
        } elseif ($object instanceof VersionInfo) {
            $object = $object->getContentInfo();
        } elseif (!$object instanceof ContentInfo) {
            throw new InvalidArgumentException(
                '$object',
                'Must be of type: ContentCreateStruct, Content, VersionInfo or ContentInfo'
            );
        }

        // Load locations if no specific placement was provided
        if (empty($targets)) {
            if ($object->published) {
                $targets = $this->persistence->locationHandler()->loadLocationsByContent($object->id);
            } else {
                // @todo Need support for draft locations to to work correctly
                $targets = $this->persistence->locationHandler()->loadParentLocationsForDraftContent($object->id);
            }
        }

        // Parent Limitations are usually used by content/create where target is specified,
        // so we return false if not provided.
        if (empty($targets)) {
            return false;
        }

        foreach ($targets as $target) {
            if ($target instanceof Location || $target instanceof SPILocation) {
                $depth = $target->depth;
            } else {
                throw new InvalidArgumentException(
                    '$targets',
                    'Must contain objects of type: Location'
                );
            }

            // All placements must match
            if (!in_array($depth, $value->limitationValues)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate permissions for ContentCreateStruct against LocationCreateStruct placements.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $targets does not contain
     *         objects of type LocationCreateStruct
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param array|null $targets
     *
     * @return bool
     */
    protected function evaluateForContentCreateStruct(APILimitationValue $value, array $targets = null)
    {
        // If targets is empty/null return false as user does not have access
        // to content w/o location with this limitation
        if (empty($targets)) {
            return false;
        }

        foreach ($targets as $target) {
            if (!$target instanceof LocationCreateStruct) {
                throw new InvalidArgumentException(
                    '$targets',
                    'If $object is ContentCreateStruct must contain objects of type: LocationCreateStruct'
                );
            }

            $depth = $this->persistence->locationHandler()->load($target->parentLocationId)->depth;

            // All placements must match
            if (!in_array($depth, $value->limitationValues)) {
                return false;
            }
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
