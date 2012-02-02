<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\Query\Criterion\Location
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace eZ\Publish\SPI\Persistence\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\Query\Criterion,
    eZ\Publish\SPI\Persistence\Content\Query\Criterion\Operator\Specifications,
    eZ\Publish\SPI\Persistence\Content\Query\CriterionInterface;

/**
 * A criterion that matches content based on its own location id
 *
 * Parent location id is done using {@see ParentLocationId}
 *
 * Supported operators:
 * - IN: matches against a list of location ids
 * - EQ: matches against a unique location id
 */
class LocationId extends Criterion implements CriterionInterface
{
    /**
     * Creates a new LocationId criterion
     *
     * @param null $target Not used
     * @param string $operator
     *        Possible values:
     *        - Operator::IN: match against a list of locationId. $value must be an array of locationId
     *        - Operator::EQ: match against a single locationId. $value must be a single locationId
     * @param integer|array(integer) One or more locationId that must be matched
     *
     * @throw InvalidArgumentException if a non numeric id is given
     * @throw InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $value )
    {
        parent::__construct( null, null, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
        );
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $value );
    }
}
?>
