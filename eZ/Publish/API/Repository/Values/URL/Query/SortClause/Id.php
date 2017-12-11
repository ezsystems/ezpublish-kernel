<?php

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
