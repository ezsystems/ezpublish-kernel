<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Limitation;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation as APIParentContentTypeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;

/**
 * ParentContentTypeLimitation is a Content limitation
 */
class ParentContentTypeLimitationType extends AbstractPersistenceLimitationType implements SPILimitationTypeInterface
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
    public function acceptValue( APILimitationValue $limitationValue )
    {
        if ( !$limitationValue instanceof APIParentContentTypeLimitation )
        {
            throw new InvalidArgumentType( "\$limitationValue", "APIParentContentTypeLimitation", $limitationValue );
        }
        else if ( !is_array( $limitationValue->limitationValues ) )
        {
            throw new InvalidArgumentType( "\$limitationValue->limitationValues", "array", $limitationValue->limitationValues );
        }

        foreach ( $limitationValue->limitationValues as $key => $id )
        {
            if ( !is_string( $id ) && !is_int( $id ) )
            {
                throw new InvalidArgumentType( "\$limitationValue->limitationValues[{$key}]", "int|string", $id );
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
    public function validate( APILimitationValue $limitationValue )
    {
        $validationErrors = array();
        foreach ( $limitationValue->limitationValues as $key => $id )
        {
            try
            {
                $this->persistence->contentTypeHandler()->load( $id );
            }
            catch ( APINotFoundException $e )
            {
                $validationErrors[] = new ValidationError(
                    "limitationValues[%key%] => '%value%' does not exist in the backend",
                    null,
                    array(
                        "value" => $id,
                        "key" => $key
                    )
                );
            }
        }
        return $validationErrors;
    }

    /**
     * Create the Limitation Value
     *
     * @param mixed[] $limitationValues
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation
     */
    public function buildValue( array $limitationValues )
    {
        return new APIParentContentTypeLimitation( array( 'limitationValues' => $limitationValues ) );
    }

    /**
     * Evaluate permission against content & target(placement/parent/assignment)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If any of the arguments are invalid
     *         Example: If LimitationValue is instance of ContentTypeLimitationValue, and Type is SectionLimitationType.
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If value of the LimitationValue is unsupported
     *         Example if OwnerLimitationValue->limitationValues[0] is not one of: [ 1,  2 ]
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Values\User\User $currentUser
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\ValueObject[] $targets An array of location, parent or "assignment" value objects
     *
     * @return boolean
     */
    public function evaluate( APILimitationValue $value, APIUser $currentUser, ValueObject $object, array $targets = array() )
    {
        if ( !$value instanceof APIParentContentTypeLimitation )
            throw new InvalidArgumentException( '$value', 'Must be of type: APIParentContentTypeLimitation' );

        if ( empty( $targets ) )
        {
            return false;
        }

        foreach ( $targets as $target )
        {
            if ( $target instanceof LocationCreateStruct )
            {
                $target = $this->persistence->locationHandler()->load( $target->parentLocationId );
            }

            if ( $target instanceof Location )
            {
                $contentTypeId = $target->getContentInfo()->contentTypeId;
            }
            else if ( $target instanceof SPILocation )
            {
                $spiContentInfo = $this->persistence->contentHandler()->loadContentInfo( $target->contentId );
                $contentTypeId = $spiContentInfo->contentTypeId;
            }
            else
            {
                throw new InvalidArgumentException(
                    '$targets',
                    'Must contain objects of type: Location or LocationCreateStruct'
                );
            }

            if ( !in_array( $contentTypeId, $value->limitationValues ) )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns Criterion for use in find() query
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Values\User\User $currentUser
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface
     */
    public function getCriterion( APILimitationValue $value, APIUser $currentUser )
    {
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException( __METHOD__ );
    }

    /**
     * Returns info on valid $limitationValues
     *
     * @return mixed[]|int In case of array, a hash with key as valid limitations value and value as human readable name
     *                     of that option, in case of int on of VALUE_SCHEMA_ constants.
     */
    public function valueSchema()
    {
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException( __METHOD__ );
    }
}
