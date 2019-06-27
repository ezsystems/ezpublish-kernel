<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\SiteAccessLimitation class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Limitation;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\API\Repository\Values\User\Limitation\SiteAccessLimitation as APISiteAccessLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;

/**
 * SiteAccessLimitation is a User limitation.
 */
class SiteAccessLimitationType implements SPILimitationTypeInterface
{
    /** @var array */
    private $siteAccessList = [];

    public function __construct(array $siteAccessList = [])
    {
        foreach ($siteAccessList as $sa) {
            $this->siteAccessList[$this->generateSiteAccessValue($sa)] = $sa;
        }
    }

    /**
     * Generates the SiteAccess value as CRC32.
     *
     * @param string $sa
     * @return string
     */
    private function generateSiteAccessValue($sa)
    {
        return sprintf('%u', crc32($sa));
    }

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
        if (!$limitationValue instanceof APISiteAccessLimitation) {
            throw new InvalidArgumentType('$limitationValue', 'APISiteAccessLimitation', $limitationValue);
        } elseif (!is_array($limitationValue->limitationValues)) {
            throw new InvalidArgumentType('$limitationValue->limitationValues', 'array', $limitationValue->limitationValues);
        }

        foreach ($limitationValue->limitationValues as $key => $value) {
            // Value must be a CRC32, so can be either as string or integer.
            if (!is_string($value) && !is_int($value)) {
                throw new InvalidArgumentType("\$limitationValue->limitationValues[{$key}]", 'string or integer', $value);
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
            if (!isset($this->siteAccessList[$value])) {
                $validationErrors[] = new ValidationError(
                    "\$limitationValue->limitationValues[%key%] => Invalid SiteAccess value \"$value\"",
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
        return new APISiteAccessLimitation(['limitationValues' => $limitationValues]);
    }

    /**
     * Evaluate permission against content & target(placement/parent/assignment).
     *
     * SiteAccess limitation takes a SiteAccess as ValueObject, and is hence like in legacy only suitable for user/login
     * and similar policies.
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
        if (!$value instanceof APISiteAccessLimitation) {
            throw new InvalidArgumentException('$value', 'Must be of type: APISiteAccessLimitation');
        }

        if (!$object instanceof SiteAccess) {
            throw new InvalidArgumentException('$object', 'Must be of type: SiteAccess');
        }

        if (empty($value->limitationValues)) {
            return false;
        }

        if (empty($object->name)) {
            return false;
        }

        $currentSiteAccessHash = $this->generateSiteAccessValue($object->name);

        return in_array($currentSiteAccessHash, $value->limitationValues);
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
