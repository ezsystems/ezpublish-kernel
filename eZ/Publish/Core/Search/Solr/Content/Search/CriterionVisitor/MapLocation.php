<?php
/**
 * File containing the MapLocation criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Solr\Content\Search\FieldMap;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the MapLocation criterion
 */
abstract class MapLocation extends CriterionVisitor
{
    /**
     * Field map
     *
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldMap
     */
    protected $fieldMap;

    /**
     * Identifier of the field type that criterion can handle
     *
     * @var string
     */
    protected $fieldTypeIdentifier;

    /**
     * Name of the field type's indexed field that criterion can handle
     *
     * @var string
     */
    protected $fieldName;

    /**
     * Create from FieldMap, FieldType identifier and field name.
     *
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldMap $fieldMap
     * @param string $fieldTypeIdentifier
     * @param string $fieldName
     */
    public function __construct( FieldMap $fieldMap, $fieldTypeIdentifier, $fieldName )
    {
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
        $this->fieldName = $fieldName;

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
