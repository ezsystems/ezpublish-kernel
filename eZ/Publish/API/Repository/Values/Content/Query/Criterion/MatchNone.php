<?php
/**
 * This file is part of the eZ Publish package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;

/**
 * A criterion that just matches nothing
 *
 * Useful for BlockingLimitation type, where a limitation is typically missing and needs to
 * tell the system should block everything within the OR conditions it might be part of.
 */
class MatchNone extends Criterion implements CriterionInterface
{
    public function __construct()
    {
        // Do NOT call parent constructor. It tries to be too smart.
    }

    public function getSpecifications()
    {
        return array();
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self();
    }
}
