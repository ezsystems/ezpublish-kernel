<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Ancestor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\MatchNone;

final class AncestorsQueryType extends AbstractLocationQueryType
{
    public static function getName(): string
    {
        return 'eZ:Ancestors';
    }

    protected function getQueryFilter(array $parameters): Criterion
    {
        $location = $this->resolveLocation($parameters);

        if ($location === null) {
            return new MatchNone();
        }

        return new LogicalAnd([
            new Ancestor($location->pathString),
            new LogicalNot(
                new LocationId($location->id)
            ),
        ]);
    }
}
