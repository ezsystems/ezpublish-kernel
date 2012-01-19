<?php
namespace ezp\PublicAPI\Values\Content\Query\Criterion;

use ezp\PublicAPI\Values\Content\Query\Criterion;

/**
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
        foreach ( $criteria as $criterion )
        {
            if ( !$criterion instanceof Criterion )
            {
                throw new InvalidArgumentException( "Only Criterion objects are accepted" );
            }
            $this->criteria[] = $criterion;
        }
    }

    /**
     * The set of criteria combined by the logical operator
     * @var array(Criterion)
     */
    public $criteria = array();
}
?>
