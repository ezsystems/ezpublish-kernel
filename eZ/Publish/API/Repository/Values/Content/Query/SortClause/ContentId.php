<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentId class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Sets sort direction on Content ID for a content query
 *
 * Especially useful to get reproducible search results in tests.
 */
class ContentId extends SortClause
{
    /**
     * Constructs a new ContentId SortClause
     * @param string $sortDirection
     */
    public function __construct( $sortDirection = Query::SORT_ASC )
    {
        parent::__construct( 'content_id', $sortDirection );
    }
}
