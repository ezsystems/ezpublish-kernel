<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\URL\Query\SortClause;

use eZ\Publish\API\Repository\Values\URL\Query\SortClause;

class Id extends SortClause
{
    /**
     * Constructs a new Id SortClause.
     *
     * @param string $sortDirection
     */
    public function __construct($sortDirection = self::SORT_ASC)
    {
        parent::__construct('id', $sortDirection);
    }
}
