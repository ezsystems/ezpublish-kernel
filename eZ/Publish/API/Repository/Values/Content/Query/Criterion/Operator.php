<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Operators struct.
 *
 * Note that the method is abstract as there is no point in instantiating it
 */
abstract class Operator
{
    public const EQ = '=';
    public const GT = '>';
    public const GTE = '>=';
    public const LT = '<';
    public const LTE = '<=';
    public const IN = 'in';
    public const BETWEEN = 'between';

    /**
     * Does a lookup where a the value _can_ contain a "*" (a wildcard) in order to match a pattern.
     *
     * E.g: $criterion->value = "Oper*or";
     */
    public const LIKE = 'like';
    public const CONTAINS = 'contains';
}
