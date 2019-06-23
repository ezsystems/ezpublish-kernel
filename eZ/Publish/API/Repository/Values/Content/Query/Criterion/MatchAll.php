<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\MatchAll class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * A criterion that just matches everything.
 */
class MatchAll extends Criterion
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
        return [];
    }

    /**
     * @deprecated since 7.2, will be removed in 8.0. Use the constructor directly instead.
     */
    public static function createFromQueryBuilder($target, $operator, $value)
    {
        @trigger_error('The ' . __METHOD__ . ' method is deprecated since version 7.2 and will be removed in 8.0.', E_USER_DEPRECATED);

        return new self();
    }
}
