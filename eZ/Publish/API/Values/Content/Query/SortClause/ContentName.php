<?php
namespace eZ\Publish\API\Values\Content\Query\SortClause;

use eZ\Publish\API\Values\Content\Query,
    eZ\Publish\API\Values\Content\Query\SortClause;

/**
 * Sets sort direction on Content name for a content query
 */
class ContentName extends SortClause
{
    /**
     * Constructs a new ContentName SortClause
     * @param string $sortDirection
     */
    public function __construct( $sortDirection = Query::SORT_ASC )
    {
        parent::__construct( 'content_name', $sortDirection );
    }
}
