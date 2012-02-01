<?php
namespace eZ\Publish\API\Values\Content\Query\SortClause;

use eZ\Publish\API\Values\Content\Query\SortClause\Target\FieldSortClauseTarget;

use eZ\Publish\API\Values\Content\Query,
    eZ\Publish\API\Values\Content\Query\SortClause;

/**
 * Sets sort direction on a field value for a content query
 */
class FieldSortClause extends SortClause
{
    /**
     * Constructs a new Field SortClause on Type $typeIdentifier and Field $fieldIdentifier
     * @param string $typeIdentifier
     * @param string $fieldIdentifier
     * @param string $sortDirection
     */
    public function __construct( $typeIdentifier, $fieldIdentifier, $sortDirection = Query::SORT_ASC )
    {
        parent::__construct( 'field', $sortDirection, new FieldSortClauseTarget( $typeIdentifier, $fieldIdentifier ) );
    }
}
