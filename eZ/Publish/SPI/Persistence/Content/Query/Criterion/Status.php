<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\Content\Query\Criterion\Status class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\SPI\Persistence\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\Query\Criterion,
    eZ\Publish\SPI\Persistence\Content\Query\Criterion\Operator\Specifications,
    eZ\Publish\SPI\Persistence\Content\Query\CriterionInterface,
    InvalidArgumentException;

/**
 * A criterion that matches content based on its status
 *
 * Multiple statuses can be used, asn array of statuses
 */
class Status extends Criterion implements CriterionInterface
{
    /**
     * Creates a new Status criterion
     *
     * @param string|string[] $value Status: self::STATUS_ARCHIVED, self::STATUS_DRAFT, self::STATUS_PUBLISHED
     */
    public function __construct( $value )
    {
        if ( !is_array( $value ) )
        {
            $testValue = array( $value );
        }
        else
        {
            $testValue = $value;
        }
        foreach ( $testValue as $statusValue )
        {
            if ( !in_array( $statusValue, array( self::STATUS_ARCHIVED, self::STATUS_DRAFT, self::STATUS_PUBLISHED ) ) )
            {
                throw new InvalidArgumentException( "Invalid status $statusValue" );
            }
        }
        parent::__construct( null, null, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_STRING
            ),
        );
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $value );
    }

    /**
     * Status constant: draft
     */
    const STATUS_DRAFT = "draft";

    /**
     * Status constant: published
     */
    const STATUS_PUBLISHED = "published";

    /**
     * Status constant: archived
     */
    const STATUS_ARCHIVED = "archived";
}
?>
