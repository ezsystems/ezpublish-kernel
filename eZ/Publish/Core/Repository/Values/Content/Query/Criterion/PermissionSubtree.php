<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\PermissionSubtree class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree as APISubtreeCriterion;

/**
 * Criterion that matches content that belongs to a given (list of) Subtree(s).
 *
 * Content will be matched if it is part of at least one of the given subtree path strings
 *
 * This is a internal subtree criterion intended for use by permission system (SubtreeLimitationType) only!
 * And will be applied by SQL based search engines on Content Search to avoid performance problems.
 * @see https://jira.ez.no/browse/EZP-23037
 */
class PermissionSubtree extends APISubtreeCriterion
{
    public static function createFromQueryBuilder($target, $operator, $value)
    {
        return new self($value);
    }
}
