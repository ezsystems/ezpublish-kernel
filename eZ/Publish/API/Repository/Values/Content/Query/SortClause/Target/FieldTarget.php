<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target;

/**
 * Struct that stores extra target informations for a SortClause object.
 */
class FieldTarget extends Target
{
    /**
     * Identifier of a targeted Field ContentType.
     *
     * @var string
     */
    public $typeIdentifier;

    /**
     * Identifier of a targeted Field FieldDefinition.
     *
     * @var string
     */
    public $fieldIdentifier;

    public function __construct($typeIdentifier, $fieldIdentifier)
    {
        $this->typeIdentifier = $typeIdentifier;
        $this->fieldIdentifier = $fieldIdentifier;
    }
}
