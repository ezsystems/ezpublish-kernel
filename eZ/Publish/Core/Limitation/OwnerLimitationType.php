<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation class.
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
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation as APIOwnerLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * OwnerLimitation is a Content limitation
 */
class OwnerLimitationType implements SPILimitationTypeInterface
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
        return new APIOwnerLimitation( array( 'limitationValues' => $limitationValues ) );
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
     *
     * @todo Add support for $limitationValues[0] == 2 when session values can be injected somehow, or deprecate
     */
    public function evaluate( APILimitationValue $value, Repository $repository, ValueObject $object, ValueObject $target = null )
    {
        if ( !$value instanceof APIOwnerLimitation )
            throw new InvalidArgumentException( '$value', 'Must be of type: APIOwnerLimitation' );

        if ( $value->limitationValues[0] != 1 && $value->limitationValues[0] != 2 )
        {
            throw new BadStateException(
                'Owner limitation',
                'expected limitation value to be 1 or 2 but got:' . $value->limitationValues[0]
            );
        }

        if ( $object instanceof Content )
            $object = $object->getVersionInfo()->getContentInfo();
        else if ( $object instanceof VersionInfo )
            $object = $object->getContentInfo();
        else if ( !$object instanceof ContentInfo )
            throw new InvalidArgumentException( '$object', 'Must be of type: Content, VersionInfo or ContentInfo' );

        /**
         * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo $object
         */
        return $object->ownerId === $repository->getCurrentUser()->id;
    }

    /**
     * Return Criterion for use in find() query
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Repository $repository
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface
     *
     * @todo Add support for $limitationValues[0] == 2 when session values can be injected somehow, or deprecate
     */
    public function getCriterion( APILimitationValue $value, Repository $repository )
    {
        if ( empty( $value->limitationValues )  )// no limitation values
            throw new \RuntimeException( "\$value->limitationValues is empty, it should not have been stored in the first place" );

        if ( $value->limitationValues[0] != 1 && $value->limitationValues[0] != 2 )
        {
            throw new BadStateException(
                'Parent User Group limitation',
                'expected limitation value to be 1 but got:' . $value->limitationValues[0]
            );
        }

        return new Criterion\UserMetadata(
            Criterion\UserMetadata::OWNER,
            Criterion\Operator::EQ,
            $repository->getCurrentUser()->id
        );
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
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException( __METHOD__ );
    }
}
