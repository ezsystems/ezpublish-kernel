<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\UserGroupLimitation class.
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
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\User\Limitation\UserGroupLimitation as APIUserGroupLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;
use eZ\Publish\SPI\Persistence\Handler as SPIPersistenceHandler;

/**
 * UserGroupLimitation is a Content Limitation
 */
class UserGroupLimitationType implements SPILimitationTypeInterface
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
        return new APIUserGroupLimitation( array( 'limitationValues' => $limitationValues ) );
    }

    /**
     * Evaluate permission against content & target(placement/parent/assignment)
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If any of the arguments are invalid
     *         Example: If LimitationValue is instance of ContentTypeLimitationValue, and Type is SectionLimitationType.
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException If value of the LimitationValue is unsupported
     *         Example if OwnerLimitationValue->limitationValues[0] is not one of: [ 1 ]
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
        if ( !$value instanceof APIUserGroupLimitation )
        {
            throw new InvalidArgumentException( '$value', 'Must be of type: APIUserGroupLimitation' );
        }

        if ( $value->limitationValues[0] != 1 )
        {
            throw new BadStateException(
                'Parent User Group limitation',
                'expected limitation value to be 1 but got:' . $value->limitationValues[0]
            );
        }

        if ( $object instanceof Content )
        {
            $object = $object->getVersionInfo()->getContentInfo();
        }
        else if ( $object instanceof VersionInfo )
        {
            $object = $object->getContentInfo();
        }
        else if ( !$object instanceof ContentInfo && !$object instanceof ContentCreateStruct )
        {
            throw new InvalidArgumentException(
                '$object',
                'Must be of type: ContentCreateStruct, Content, VersionInfo or ContentInfo'
            );
        }

         /**
          * @var $object ContentInfo|ContentCreateStruct
          */
        if ( $object->ownerId === $currentUser->id )
            return true;

        /**
         * As long as SPI userHandler and API UserService does not speak the same language, this is the ugly truth;
         */
        $locationHandler = $this->persistence->locationHandler();
        $ownerLocations = $locationHandler->loadLocationsByContent( $object->ownerId );
        if ( empty( $ownerLocations ) )
            return false;

        $currentUserLocations = $locationHandler->loadLocationsByContent( $currentUser->id );
        if ( empty( $currentUserLocations ) )
            return false;

        // @todo Needs to take care of inherited groups as well when UserHandler gets knowledge about user groups
        foreach ( $ownerLocations as $ownerLocation )
        {
            foreach ( $currentUserLocations as $currentUserLocation )
            {
                if ( $ownerLocation->parentId === $currentUserLocation->parentId )
                    return true;
            }
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

        if ( $value->limitationValues[0] != 1 )
        {
            throw new BadStateException(
                'Parent User Group limitation',
                'expected limitation value to be 1 but got:' . $value->limitationValues[0]
            );
        }

        $groupIds = array();
        // @todo Needs to take care of inherited groups as well when UserHandler gets knowledge about user groups
        $currentUserLocations = $this->persistence->locationHandler()->loadLocationsByContent( $currentUser->id );
        if ( !empty( $currentUserLocations ) )
        {
            foreach ( $currentUserLocations as $currentUserLocation )
                $groupIds[] = $currentUserLocation->parentId;
        }

        return new Criterion\UserMetadata(
            Criterion\UserMetadata::GROUP,
            Criterion\Operator::IN,
            $groupIds
        );
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
