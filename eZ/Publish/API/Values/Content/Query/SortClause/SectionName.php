<?php
namespace eZ\Publish\API\Values\Content\Query\SortClause;

use eZ\Publish\API\Values\Content\Query,
    eZ\Publish\API\Values\Content\Query\SortClause;

/**
 * Sets sort direction on Section name for a content query
 */
class SectionName extends SortClause
{
    /**
     * Constructs a new SectionName SortClause
     * @param string $sortDirection
     */
    public function __construct( $sortDirection = Query::SORT_ASC )
    {
        parent::__construct( 'section_name', $sortDirection );
    }
}
