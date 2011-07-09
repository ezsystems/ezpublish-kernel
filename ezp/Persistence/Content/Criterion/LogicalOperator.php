<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\LogicalOperator class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;

/**
 * @package ezp.persistence.content.criteria
 *
 * Note that the class should ideally have been in a Logical namespace, but it would have then be named 'And',
 * and 'And' is a PHP reserved word.
 */
abstract class LogicalOperator extends Criterion
{
    /**
     * Creates a Logic operation with the given criteria
     *
     * @param array(Criterion) $criteria
     */
    public function __construct( array $criteria )
    {
        foreach( $criteria as $criterion )
        {
            if ( !$arg instanceof Criterion )
            {
                throw new \InvalidArgumentException( "Only Criterion objects are accepted" );
            }
            $this->criteria[] = $arg;
        }
    }

    /**
     * The set of criteria combined by the logical operator
     * @var array(Criterion)
     */
    public $criteria;
}
?>
