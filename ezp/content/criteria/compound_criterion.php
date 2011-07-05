<?php
/**
 * File containing Criterion class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content/criteria
 */
namespace ezp\content\Criteria;

/**
 * Compound criterion class. A compound criterion binds together multiple criteria with a logical (OR/AND) operator
 * A criterion is a logical association between 2 or more criterias
 */
abstract class CompoundCriterion
{
    /**
     * Criterias contained in current criterion
     * @var array( Criteria )
     */
    protected $operator = Operator::L_AND;

    /**
     * Criteria in the compound
     * @var array(Criterion)
     */
    protected $criteria = array();

    /**
     * Constructor
     *
     * @param Operator $operator
     * @param Criteria $criteriaOne
     * @param Criteria $criteriaTwo$..
     *
     * @throws \InvalidArgumentException If at least one of passed criterias is not a valid Criteria object
     */
    public function __construct( $operator, Criteria $criterionOne, Criteria $criteriaTwo )
    {
        foreach ( $criterias as $c )
        {
            if ( !$c instanceof Criteria )
            {
                throw new \InvalidArgumentException( "At least one of provided criterias is not a valid Criteria object" );
            }

            $this->criterias[] = $c;
        }
    }
}
?>
