<?php
/**
 *
 * @package eZ\Publish\API\Values\Content\Query
 */
namespace eZ\Publish\API\Values\Content\Query\Criterion;

/**
 * Operators struct
 *
 * Note that the method is abstract as there is no point in instanciating it
 * @package eZ\Publish\API\Values\Content\Query
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
