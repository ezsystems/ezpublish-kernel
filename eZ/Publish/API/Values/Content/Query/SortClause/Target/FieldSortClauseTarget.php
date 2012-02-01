<?php
/**
 *
 *
 * @package eZ\Publish\API\Values\Content\Query
 */
namespace eZ\Publish\API\Values\Content\Query\SortClause\Target;

use eZ\Publish\API\Values\Content\Query\SortClause\Target as SortClauseTarget;

/**
 * Struct that stores extra target informations for a SortClause object
 * @package eZ\Publish\API\Values\Content\Query
 */
class FieldSortClauseTarget extends SortClauseTarget
{
    public $typeIdentifier;
    public $fieldIdentifier;

    public function __construct( $typeIdentifier, $fieldIdentifier )
    {
        $this->typeIdentifier = $typeIdentifier;
        $this->fieldIdentifier = $fieldIdentifier;
    }
}
