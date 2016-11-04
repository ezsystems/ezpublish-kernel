<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\SectionName class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Sets sort direction on Section name for a content query.
 */
class SectionName extends SortClause
{
    /**
     * Constructs a new SectionName SortClause.
     *
     * @param string $sortDirection
     */
    public function __construct($sortDirection = Query::SORT_ASC)
    {
        parent::__construct('section_name', $sortDirection);
    }
}
