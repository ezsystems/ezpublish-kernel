<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Sets sort direction on a field value for a content query
 */
class Field extends SortClause
{
    /**
     * Constructs a new Field SortClause on Type $typeIdentifier and Field $fieldIdentifier
     * @param string $typeIdentifier
     * @param string $fieldIdentifier
     * @param string $sortDirection
     */
    public function __construct( $typeIdentifier, $fieldIdentifier, $sortDirection = Query::SORT_ASC )
    {
        parent::__construct( 'field', $sortDirection, new FieldTarget( $typeIdentifier, $fieldIdentifier ) );
    }
}
