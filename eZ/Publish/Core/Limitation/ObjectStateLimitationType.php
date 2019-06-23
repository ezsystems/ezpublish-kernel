<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation class.
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
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation as APIObjectStateLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;
use eZ\Publish\Core\FieldType\ValidationError;

/**
 * ObjectStateLimitation is a Content limitation.
 */
class ObjectStateLimitationType extends AbstractPersistenceLimitationType implements SPILimitationTypeInterface
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
        if (!$limitationValue instanceof APIObjectStateLimitation) {
            throw new InvalidArgumentType('$limitationValue', 'APIObjectStateLimitation', $limitationValue);
        } elseif (!is_array($limitationValue->limitationValues)) {
            throw new InvalidArgumentType('$limitationValue->limitationValues', 'array', $limitationValue->limitationValues);
        }

        foreach ($limitationValue->limitationValues as $key => $id) {
            if (!is_string($id) && !is_int($id)) {
                throw new InvalidArgumentType("\$limitationValue->limitationValues[{$key}]", 'int|string', $id);
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
        foreach ($limitationValue->limitationValues as $key => $id) {
            try {
                $this->persistence->objectStateHandler()->load($id);
            } catch (APINotFoundException $e) {
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
     * Create the Limitation Value.
     *
     * @param mixed[] $limitationValues
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    public function buildValue(array $limitationValues)
    {
        return new APIObjectStateLimitation(['limitationValues' => $limitationValues]);
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
     * @param \eZ\Publish\API\Repository\Values\ValueObject[]| $targets An array of location, parent or "assignment" value objects
     *
     * @return bool
     */
    public function evaluate(APILimitationValue $value, APIUserReference $currentUser, ValueObject $object, array $targets = null)
    {
        if (!$value instanceof APIObjectStateLimitation) {
            throw new InvalidArgumentException('$value', 'Must be of type: APIObjectStateLimitation');
        }

        $limitationValues = $value->limitationValues;

        if ($object instanceof Content) {
            $object = $object->getVersionInfo()->getContentInfo();
        } elseif ($object instanceof VersionInfo) {
            $object = $object->getContentInfo();
        } elseif (!$object instanceof ContentInfo && !$object instanceof ContentCreateStruct) {
            throw new InvalidArgumentException('$object', 'Must be of type: Content, VersionInfo or ContentInfo');
        }

        // Skip evaluating for RootLocation
        if (1 === $object->mainLocationId) {
            return true;
        }

        if (empty($limitationValues)) {
            return false;
        }

        $objectStateIdsToVerify = [];
        $objectStateHandler = $this->persistence->objectStateHandler();
        $stateGroups = $objectStateHandler->loadAllGroups();

        // First deal with unpublished content
        if ($object instanceof ContentCreateStruct || !$object->published) {
            foreach ($stateGroups as $stateGroup) {
                $states = $objectStateHandler->loadObjectStates($stateGroup->id);
                if (empty($states)) {
                    continue;
                }

                $defaultStateId = null;
                $defaultStatePriority = -1;
                foreach ($states as $state) {
                    if ($state->priority > $defaultStatePriority) {
                        $defaultStateId = $state->id;
                        $defaultStatePriority = $state->priority;
                    }
                }

                if ($defaultStateId === null) {
                    throw new BadStateException(
                        '$defaultStateId',
                        "Could not find a default state for object state group {$stateGroup->id}"
                    );
                }

                foreach ($states as $state) {
                    // check using loose types as limitation values are strings and id's can be int
                    if (in_array($state->id, $limitationValues)) {
                        $objectStateIdsToVerify[] = $defaultStateId;
                    }
                }
            }
        } else {
            foreach ($stateGroups as $stateGroup) {
                if ($this->isStateGroupUsedForLimitation($stateGroup->id, $limitationValues)) {
                    $objectStateIdsToVerify[] = $objectStateHandler->getContentState($object->id, $stateGroup->id)->id;
                }
            }
        }

        return $this->areObjectStatesMatchingTheLimitation($objectStateIdsToVerify, $limitationValues);
    }

    /**
     * Checks if the State Group contains at least one State that is used by Limitation.
     *
     * @param int $stateGroupId
     * @param string[] $limitationValues
     *
     * @return bool
     */
    private function isStateGroupUsedForLimitation($stateGroupId, array $limitationValues)
    {
        $objectStateHandler = $this->persistence->objectStateHandler();
        $states = $objectStateHandler->loadObjectStates($stateGroupId);

        foreach ($states as $state) {
            // check using loose types as limitation values are strings and id's can be int
            if (in_array($state->id, $limitationValues)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifies if all the Object States are matching the Limitation Values.
     *
     * @param int[] $objectStateIds
     * @param string[] $limitationValues
     *
     * @return bool
     */
    private function areObjectStatesMatchingTheLimitation(array $objectStateIds, array $limitationValues)
    {
        foreach ($objectStateIds as $objectStateId) {
            // check using loose types as limitation values are strings and id's can be int
            if (!in_array($objectStateId, $limitationValues)) {
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
     * @return \eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface|\eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator
     */
    public function getCriterion(APILimitationValue $value, APIUserReference $currentUser)
    {
        if (empty($value->limitationValues)) {
            // no limitation values
            throw new \RuntimeException('$value->limitationValues is empty, it should not have been stored in the first place');
        }

        if (!isset($value->limitationValues[1])) {
            // 1 limitation value: EQ operation
            return new Criterion\ObjectStateId($value->limitationValues[0]);
        }

        $groupedLimitationValues = $this->groupLimitationValues($value->limitationValues);

        if (count($groupedLimitationValues) === 1) {
            // one group, several limitation values: IN operation
            return new Criterion\ObjectStateId($groupedLimitationValues[0]);
        }

        // limitations from different groups require logical AND between them
        $criterions = [];
        foreach ($groupedLimitationValues as $limitationGroup) {
            $criterions[] = new Criterion\ObjectStateId($limitationGroup);
        }

        return new Criterion\LogicalAnd($criterions);
    }

    /**
     * Groups limitation values by the State Group.
     *
     * @param string[] $limitationValues
     *
     * @return int[][]
     */
    private function groupLimitationValues(array $limitationValues)
    {
        $objectStateHandler = $this->persistence->objectStateHandler();
        $stateGroups = $objectStateHandler->loadAllGroups();
        $groupedLimitationValues = [];
        foreach ($stateGroups as $stateGroup) {
            $states = $objectStateHandler->loadObjectStates($stateGroup->id);
            $stateIds = array_map(function ($state) {
                return $state->id;
            }, $states);
            $limitationValuesGroup = array_intersect($stateIds, $limitationValues);
            if (!empty($limitationValuesGroup)) {
                $groupedLimitationValues[] = array_values($limitationValuesGroup);
            }
        }

        return $groupedLimitationValues;
    }

    /**
     * Returns info on valid $limitationValues.
     *
     * @return mixed[]|int In case of array, a hash with key as valid limitations value and value as human readable name
     *                     of that option, in case of int on of VALUE_SCHEMA_ constants.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    public function valueSchema()
    {
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException(__METHOD__);
    }
}
