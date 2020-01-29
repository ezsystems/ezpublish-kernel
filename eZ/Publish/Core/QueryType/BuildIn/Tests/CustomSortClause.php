<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\Tests;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

final class CustomSortClause extends SortClause
{
    public function __construct(string $foo, string $bar, string $baz, $sortDirection = Query::SORT_ASC)
    {
        parent::__construct('custom', $sortDirection, [
            'foo' => $foo,
            'bar' => $bar,
            'baz' => $baz,
        ]);
    }
}
