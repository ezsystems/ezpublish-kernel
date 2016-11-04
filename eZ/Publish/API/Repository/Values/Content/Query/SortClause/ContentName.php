<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentName class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Sets sort direction on Content name for a content query.
 */
class ContentName extends SortClause
{
    /**
     * Constructs a new ContentName SortClause.
     *
     * @param string $sortDirection
     */
    public function __construct($sortDirection = Query::SORT_ASC)
    {
        parent::__construct('content_name', $sortDirection);
    }
}
