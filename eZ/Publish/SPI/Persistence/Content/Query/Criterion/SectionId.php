<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\Query\Criterion\Section
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\SPI\Persistence\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\Query\Criterion,
    eZ\Publish\SPI\Persistence\Content\Query\Criterion\Operator\Specifications,
    eZ\Publish\SPI\Persistence\Content\Query\CriterionInterface;

/**
 * SectionId Criterion
 *
 * Will match content that belongs to one of the given sections
 */
class SectionId extends Criterion implements CriterionInterface
{
    /**
     * Creates a new Section criterion
     *
     * Matches the content against one or more sectionId
     *
     * @param null $target Not used
     * @param string $operator
     *        Possible values:
     *        - Operator::IN: match against a list of sectionId. $value must be an array of sectionId
     *        - Operator::EQ: match against a single sectionId. $value must be a single sectionId
     * @param integer|array(integer) One or more sectionId that must be matched
     *
     * @throw InvalidArgumentException if a non numeric id is given
     * @throw InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $value  )
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
