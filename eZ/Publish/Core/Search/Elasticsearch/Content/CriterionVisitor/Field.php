<?php
/**
 * File containing the abstract Field criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldMap;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Base class for Field criterion visitors
 */
abstract class Field extends FieldFilterBase
{
    /**
     * Field map
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldMap
     */
    protected $fieldMap;

    /**
     * Create from FieldMap
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldMap $fieldMap
     */
    public function __construct( FieldMap $fieldMap )
    {
        $this->fieldMap = $fieldMap;
    }

    /**
     * Get field names
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $fieldDefinitionIdentifier
     * @param string $fieldTypeIdentifier
     * @param string $name
     *
     * @return array
     */
    protected function getFieldNames(
        Criterion $criterion,
        $fieldDefinitionIdentifier,
        $fieldTypeIdentifier = null,
        $name = null
    )
    {
        return $this->fieldMap->getFieldNames(
            $criterion,
            $fieldDefinitionIdentifier,
            $fieldTypeIdentifier,
            $name
        );
    }
}
