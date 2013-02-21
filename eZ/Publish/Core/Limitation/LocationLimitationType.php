<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Limitation;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation as APILocationLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;
use eZ\Publish\SPI\Persistence\Handler as SPIPersistenceHandler;

/**
 * LocationLimitation is a Content limitation
 */
class LocationLimitationType implements SPILimitationTypeInterface
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistence;

    /**
     * @param \eZ\Publish\SPI\Persistence\Handler $persistence
     */
    public function __construct( SPIPersistenceHandler $persistence )
    {
        $this->persistence = $persistence;
    }

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
    public function acceptValue( APILimitationValue $limitationValue )
    {
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException( __METHOD__ );
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
        return new APILocationLimitation( array( 'limitationValues' => $limitationValues ) );
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
     * @param \eZ\Publish\API\Repository\Values\ValueObject|null $target The location, parent or "assignment" value object
     *
     * @return boolean
     */
    public function evaluate( APILimitationValue $value, APIUser $currentUser, ValueObject $object, ValueObject $target = null )
    {
        if ( !$value instanceof APILocationLimitation )
        {
            throw new InvalidArgumentException( '$value', 'Must be of type: APILocationLimitation' );
        }

        if ( $object instanceof Content )
        {
            $object = $object->getVersionInfo()->getContentInfo();
        }
        else if ( $object instanceof VersionInfo )
        {
            $object = $object->getContentInfo();
        }
        else if ( $object instanceof ContentCreateStruct )
        {
            // If target is null return false as user does not have access to content w/o location with this limitation
            if ( $target === null )
                return false;

            if ( !$target instanceof LocationCreateStruct )
            {
                throw new InvalidArgumentException(
                    '$object',
                    'Cannot be ContentCreateStruct unless $target is LocationCreateStruct'
                );
            }
        }
        else if ( !$object instanceof ContentInfo )
        {
            throw new InvalidArgumentException(
                '$object',
                'Must be of type: ContentCreateStruct, Content, VersionInfo or ContentInfo'
            );
        }

        if ( $target !== null && !$target instanceof Location && !$target instanceof LocationCreateStruct )
        {
            throw new InvalidArgumentException( '$target', 'Must be of type: Location' );
        }

        if ( empty( $value->limitationValues ) )
        {
            return false;
        }

        /**
         * Use $target if provided, optionally used to check the specific location instead of all
         * e.g.: 'remove' in the context of removal of a specific location, or in case of 'create'
         */
        if ( $target instanceof Location )
        {
            return in_array( $target->id, $value->limitationValues );
        }
        if ( $target instanceof LocationCreateStruct )
        {
            return in_array( $target->parentLocationId, $value->limitationValues );
        }

        /**
         * Check all locations if no specific placement was provided
         *
         * @var $object ContentInfo
         */
        $locations = $this->persistence->locationHandler()->loadLocationsByContent( $object->id );
        foreach ( $locations as $location )
        {
            if ( in_array( $location->id, $value->limitationValues ) )
                return true;
        }
        return false;
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
        if ( empty( $value->limitationValues )  )// no limitation values
            throw new \RuntimeException( "\$value->limitationValues is empty, it should not have been stored in the first place" );

        if ( !isset( $value->limitationValues[1] ) )// 1 limitation value: EQ operation
            return new Criterion\LocationId( $value->limitationValues[0] );

        // several limitation values: IN operation
        return new Criterion\LocationId( $value->limitationValues );
    }

    /**
     * Returns info on valid $limitationValues
     *
     * @return mixed[]|int In case of array, a hash with key as valid limitations value and value as human readable name
     *                     of that option, in case of int on of VALUE_SCHEMA_ constants.
     */
    public function valueSchema()
    {
        self::VALUE_SCHEMA_LOCATION_ID;
    }
}
