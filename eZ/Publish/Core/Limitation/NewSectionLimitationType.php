<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation class.
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
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\User\Limitation\NewSectionLimitation as APINewSectionLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;

/**
 * NewSectionLimitation is a Content Limitation used on 'section' 'assign' function
 */
class NewSectionLimitationType implements SPILimitationTypeInterface
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
        return new APINewSectionLimitation( array( 'limitationValues' => $limitationValues ) );
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
        if ( !$value instanceof APINewSectionLimitation )
            throw new InvalidArgumentException( '$value', 'Must be of type: APINewSectionLimitation' );

        if ( !$object instanceof ContentInfo && !$object instanceof Content )
            throw new InvalidArgumentException( '$object', 'Must be of type: Content or ContentInfo' );

        if ( !$target instanceof Section )
            throw new InvalidArgumentException( '$target', 'Must be of type: Section' );

        if ( empty( $value->limitationValues ) )
            return false;

        /**
         * @var \eZ\Publish\API\Repository\Values\Content\Section $target
         */
        return in_array( $target->id, $value->limitationValues );
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
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException( __METHOD__ );
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
