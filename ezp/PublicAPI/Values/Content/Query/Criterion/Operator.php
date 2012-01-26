<?php
/**
 *
 * @package ezp\PublicAPI\Values\Content\Query
 */
namespace ezp\PublicAPI\Values\Content\Query\Criterion;

/**
 * Operators struct
 *
 * Note that the method is abstract as there is no point in instanciating it
 * @package ezp\PublicAPI\Values\Content\Query
 */
abstract class Operator
{
    const EQ = "=";
    const GT = ">";
    const GTE = ">=";
    const LT = "<";
    const LTE = "<=";
    const IN = "in";
    const BETWEEN = "between";
    const LIKE = "like";
}
