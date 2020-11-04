<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Sorts search results by relevance score.
 */
class Score extends SortClause
{
    public function __construct(string $sortDirection = Query::SORT_ASC)
    {
        parent::__construct('score', $sortDirection);
    }
}
