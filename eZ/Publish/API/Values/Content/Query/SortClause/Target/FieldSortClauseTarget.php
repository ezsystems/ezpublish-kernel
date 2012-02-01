<?php
/**
 *
 *
 * @package ezp\PublicAPI\Values\Content\Query
 */
namespace ezp\PublicAPI\Values\Content\Query\SortClause\Target;

use ezp\PublicAPI\Values\Content\Query\SortClause\Target as SortClauseTarget;

/**
 * Struct that stores extra target informations for a SortClause object
 * @package ezp\PublicAPI\Values\Content\Query
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
