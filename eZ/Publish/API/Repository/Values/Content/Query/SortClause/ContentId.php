<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Sets sort direction on Content ID for a content query.
 *
 * Especially useful to get reproducible search results in tests.
 *
 * Note: order will vary per search engine, depending on how Content ID is stored in the search
 * backend. For Legacy search engine IDs are stored as integers, while with Solr and Elasticsearch
 * engines they are stored as strings. Hence the difference will be basically the one between
 * numerical and alphabetical order of sorting.
 *
 * This reflects API definition of IDs as mixed type (integer or string).
 */
class ContentId extends SortClause
{
    /**
     * Constructs a new ContentId SortClause.
     *
     * @param string $sortDirection
     */
    public function __construct($sortDirection = Query::SORT_ASC)
    {
        parent::__construct('content_id', $sortDirection);
    }
}
