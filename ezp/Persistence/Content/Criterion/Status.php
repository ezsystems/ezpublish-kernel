<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\Status class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Criterion\Operator\Specifications,
    ezp\Persistence\Content\Interfaces\Criterion as CriterionInterface;

/**
 * A criterion that matches content based on its status
 *
 * Supported operators:
 * - IN: will match from a list of statuses
 * - EQ: will match against one status
 */
class Status extends Criterion implements CriterionInterface
{
    /**
     * Creates a new Status criterion
     * @param null $target Not used
     * @param string $operator eq / in
     * @param string $value Status: self::STATUS_ARCHIVED, self::STATUS_DRAFT, self::STATUS_PUBLISHED
     */
    public function __construct( $target, $operator, $value )
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
        parent::__construct( $target, $operator, $value );
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
