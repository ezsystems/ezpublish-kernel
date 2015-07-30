<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\MatchAll class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;

/**
 * A criterion that just matches everything.
 */
class MatchAll extends Criterion implements CriterionInterface
{
    /**
     * Creates a new MatchAll criterion.
     */
    public function __construct()
    {
        // Do NOT call parent constructor. It tries to be too smart.
    }

    public function getSpecifications()
    {
        return array();
    }

    public static function createFromQueryBuilder($target, $operator, $value)
    {
        return new self();
    }
}
