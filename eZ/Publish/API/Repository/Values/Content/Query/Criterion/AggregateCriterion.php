<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

abstract class AggregateCriterion extends Criterion
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion */
    public $criteria;

    public function __construct(Criterion $criteria)
    {
        $this->criteria = $criteria;
    }

    public function getSpecifications(): array
    {
        throw new NotImplementedException('getSpecifications() not implemented for AggregateCriterion');
    }
}
