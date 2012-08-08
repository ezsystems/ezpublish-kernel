<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Limitation;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation as APIParentOwnerLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;

/**
 * ParentOwnerLimitation is a Content limitation
 */
class ParentOwnerLimitation implements SPILimitationTypeInterface
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
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException( 'acceptValue' );
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
        return new APIParentOwnerLimitation( array( 'limitationValues' => $limitationValues ) );
    }

    /**
     * Evaluate permission against content and placement
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\ValueObject $placement In 'create' limitations; this is parent
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\Base\Exceptions\BadStateException
     * @return bool
     *
     * @todo Add support for $limitationValues[0] == 2 when session values can be injected somehow
     */
    public function evaluate( APILimitationValue $value, Repository $repository, ValueObject $object, ValueObject $placement = null )
    {
        if ( !$value instanceof APIParentOwnerLimitation )
            throw new InvalidArgumentException( '$value', 'Must be of type: APIParentOwnerLimitation' );

        if ( $value->limitationValues[0] != 1 && $value->limitationValues[0] != 2 )
        {
            throw new BadStateException(
                'Parent Owner limitation',
                'expected limitation value to be 1 or 2 but got:' . $value->limitationValues[0]
            );
        }

        if ( !$object instanceof Content )
            throw new InvalidArgumentException( '$object', 'Must be of type: Content' );

        if ( $placement !== null  && !$placement instanceof Location )
            throw new InvalidArgumentException( '$placement', 'Must be of type: Location' );

        if ( $placement === null )
            return false;

        /**
         * @var \eZ\Publish\API\Repository\Values\Content\Location $placement
         */
        return $placement->getContentInfo()->ownerId === $repository->getCurrentUser()->id;
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
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException( 'getCriterion' );
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
        throw new \eZ\Publish\API\Repository\Exceptions\NotImplementedException( 'valueSchema' );
    }
}
