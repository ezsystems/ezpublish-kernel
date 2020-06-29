<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * A criterion that just matches everything.
 */
class MatchAll extends Criterion implements FilteringCriterion
{
    /**
     * Creates a new MatchAll criterion.
     */
    public function __construct()
    {
        // Do NOT call parent constructor. It tries to be too smart.
    }

    public function getSpecifications(): array
    {
        return [];
    }
}
