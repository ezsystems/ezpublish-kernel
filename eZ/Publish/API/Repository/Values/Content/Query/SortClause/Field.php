<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\Field class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
     *
     * @param string $typeIdentifier
     * @param string $fieldIdentifier
     * @param string $sortDirection
     * @param null|string $languageCode
     */
    public function __construct( $typeIdentifier, $fieldIdentifier, $sortDirection = Query::SORT_ASC, $languageCode = null )
    {
        parent::__construct(
            'field',
            $sortDirection,
            new FieldTarget( $typeIdentifier, $fieldIdentifier, $languageCode )
        );
    }
}
