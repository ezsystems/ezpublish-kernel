<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 *
 * @package eZ\Publish\API\Repository\Values\Content\Query
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target;

/**
 * Struct that stores extra target informations for a SortClause object
 * @package eZ\Publish\API\Repository\Values\Content\Query
 */
class FieldTarget extends Target
{
    public $typeIdentifier;
    public $fieldIdentifier;

    public function __construct( $typeIdentifier, $fieldIdentifier )
    {
        $this->typeIdentifier = $typeIdentifier;
        $this->fieldIdentifier = $fieldIdentifier;
    }
}
