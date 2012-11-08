<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Limitation;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation as APISubtreeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * SubtreeLimitation is a Content Limitation & a Role Limitation
 */
class SubtreeLimitationType implements SPILimitationTypeInterface
{
    /**
     * Accepts a Limitation value
     *
     * Makes sure LimitationValue object is of correct type and that ->limitationValues
     * is valid according to valueSchema().
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitationValue
     * @param \eZ\Publish\API\Repository\Repository $repository
     *
     * @return bool
     */
    public function acceptValue( APILimitationValue $limitationValue, Repository $repository )
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
        return new APISubtreeLimitation( array( 'limitationValues' => $limitationValues ) );
    }

    /**
     * Evaluate permission against content & target(placement/parent/assignment)
     *
     * NOTE: Repository is provided because not everything is available via the value object(s),
     * but use of repository in limitation functions should be avoided for performance reasons
     * if possible, especially when using un-cached parts of the api.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If any of the arguments are invalid
     *         Example: If LimitationValue is instance of ContentTypeLimitationValue, and Type is SectionLimitationType.
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If value of the LimitationValue is unsupported
     *         Example if OwnerLimitationValue->limitationValues[0] is not one of: [ 1,  2 ]
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\ValueObject $target The location, parent or "assignment" value object
     *
     * @return bool
     */
    public function evaluate( APILimitationValue $value, Repository $repository, ValueObject $object, ValueObject $target = null )
    {
        if ( !$value instanceof APISubtreeLimitation )
            throw new InvalidArgumentException( '$value', 'Must be of type: APIParentContentTypeLimitation' );

        if ( $object instanceof Content )
            $object = $object->getVersionInfo()->getContentInfo();
        else if ( $object instanceof VersionInfo )
            $object = $object->getContentInfo();
        else if ( !$object instanceof ContentInfo )
            throw new InvalidArgumentException( '$object', 'Must be of type: Content, VersionInfo or ContentInfo' );

        if ( $target !== null  && !$target instanceof Location )
            throw new InvalidArgumentException( '$target', 'Must be of type: Location' );

        if ( empty( $value->limitationValues ) )
            return false;

        /**
         * Use $target if provided, optionally used to check the specific location instead of all
         * e.g.: 'remove' in the context of removal of a specific location, or in case of 'create'
         *
         * @var \eZ\Publish\API\Repository\Values\Content\Location $target
         */
        if ( $target instanceof Location )
        {
            foreach ( $value->limitationValues as $limitationPathString )
            {
                if ( $target->pathString === $limitationPathString )
                    return true;
                if ( strpos( $target->pathString, $limitationPathString ) === 0 )
                    return true;
            }
            return false;
        }

        /**
         * Check all locations if no placement was provided
         *
         * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $object
         */
        $locations = $repository->getLocationService()->loadLocations( $object );
        foreach ( $locations as $location )
        {
            foreach ( $value->limitationValues as $limitationPathString )
            {
                if ( $location->pathString === $limitationPathString )
                    return true;
                if ( strpos( $location->pathString, $limitationPathString ) === 0 )
                    return true;
            }
        }
        return false;
    }

    /**
     * Return Criterion for use in find() query
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Repository $repository
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface
     */
    public function getCriterion( APILimitationValue $value, Repository $repository )
    {
        if ( empty( $value->limitationValues )  )// no limitation values
            throw new \RuntimeException( "\$value->limitationValues is empty, it should not have been stored in the first place" );

        if ( !isset( $value->limitationValues[1] ) )// 1 limitation value: EQ operation
            return new Criterion\Subtree( $value->limitationValues[0] );

        // several limitation values: IN operation
        return new Criterion\Subtree( $value->limitationValues );
    }

    /**
     * Return info on valid $limitationValues
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     *
     * @return mixed[]|int In case of array, a hash with key as valid limitations value and value as human readable name
     *                     of that option, in case of int on of VALUE_SCHEMA_ constants.
     */
    public function valueSchema( Repository $repository )
    {
        self::VALUE_SCHEMA_LOCATION_PATH;
    }
}
