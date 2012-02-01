<?php
namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query,
    eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Sets sort direction on Section identifier for a content query
 */
class SectionIdentifier extends SortClause
{
    /**
     * Constructs a new SectionIdentifier SortClause
     * @param string $sortDirection
     */
    public function __construct( $sortDirection = Query::SORT_ASC )
    {
        parent::__construct( 'section_identifier', $sortDirection );
    }
}
