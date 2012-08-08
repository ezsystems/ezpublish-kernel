<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\Limitation\SiteaccessLimitation class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Values\User\Limitation\SiteaccessLimitation as APISiteaccessLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;

/**
 * SiteaccessLimitation is a User limitation
 */
class SiteaccessLimitation implements SPILimitationTypeInterface
{
    /**
     * Create the Limitation Value
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
        return new APISiteaccessLimitation( array( 'limitationValues' => $limitationValues ) );
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
     */
    public function evaluate( APILimitationValue $value, Repository $repository, ValueObject $object, ValueObject $placement = null )
    {
        if ( !$value instanceof APISiteaccessLimitation )
            throw new InvalidArgumentException( '$value', 'Must be of type: APISiteaccessLimitation' );

        if ( !$object instanceof User )
            throw new InvalidArgumentException( '$object', 'Must be of type: User' );

        if ( empty( $value->limitationValues ) )
            return false;

        /**
         * @var \eZ\Publish\API\Repository\Values\Content\Content $object
         * @todo Use current siteaccess as dependency in constructor, or define a way it can be injected in this function
         * 4.x limitationValues in default 64bit mode is: sprintf( '%u', crc32( $siteAccessName ) )
         */
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
