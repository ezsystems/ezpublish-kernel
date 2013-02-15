<?php
/**
 * File containing the eZ\Publish\SPI\Limitation\Type class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Limitation;

use eZ\Publish\API\Repository\Values\ValueObject as APIValueObject;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\API\Repository\Values\User\User as APIUser;

/**
 * This interface represent the Limitation Type
 */
interface Type
{
    /**
     * Constants for valueSchema() return values
     *
     * Used in cases where a certain value is accepted but the options are to many to return as a hash of options.
     * GUI should typically present option to browse content tree to select limitation value(s).
     */
    const VALUE_SCHEMA_LOCATION_ID = 1;
    const VALUE_SCHEMA_LOCATION_PATH = 2;

    /**
     * Accepts a Limitation value
     *
     * Makes sure LimitationValue object is of correct type and that ->limitationValues
     * is valid according to valueSchema().
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitationValue
     *
     * @return boolean
     */
    public function acceptValue( APILimitationValue $limitationValue );

    /**
     * Create the Limitation Value
     *
     * The is the method to create values as Limitation type needs value knowledge anyway in acceptValue,
     * the reverse relation is provided by means of identifier lookup (Value has identifier, and so does RoleService).
     *
     * @param mixed[] $limitationValues
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    public function buildValue( array $limitationValues );

    /**
     * Evaluate permission against content and placement
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If any of the arguments are invalid
     *         Example: If LimitationValue is instance of ContentTypeLimitationValue, and Type is SectionLimitationType.
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If value of the LimitationValue is unsupported
     *         Example if OwnerLimitationValue->limitationValues[0] is not one of: [ 1,  2 ]
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Values\User\User $currentUser
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\ValueObject|null $target The location, parent or "assignment" value object
     *
     * @return boolean
     */
    public function evaluate( APILimitationValue $value, APIUser $currentUser, APIValueObject $object, APIValueObject $target = null );

    /**
     * Returns Criterion for use in find() query
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Values\User\User $currentUser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface
     */
    public function getCriterion( APILimitationValue $value, APIUser $currentUser );

    /**
     * Returns info on valid $limitationValues
     *
     * @return mixed[]|int In case of array, a hash with key as valid limitations value and value as human readable name
     *                     of that option, in case of int on of VALUE_SCHEMA_* constants.
     *                     Note: The hash might be an instance of Traversable, and not a native php array.
     */
    public function valueSchema();
}
