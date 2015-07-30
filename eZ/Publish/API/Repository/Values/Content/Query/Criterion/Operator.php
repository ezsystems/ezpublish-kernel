<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Operators struct.
 *
 * Note that the method is abstract as there is no point in instantiating it
 */
abstract class Operator
{
    const EQ = '=';
    const GT = '>';
    const GTE = '>=';
    const LT = '<';
    const LTE = '<=';
    const IN = 'in';
    const BETWEEN = 'between';
    const LIKE = 'like';
    const CONTAINS = 'contains';
}
