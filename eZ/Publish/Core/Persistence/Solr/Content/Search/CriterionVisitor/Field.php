<?php
/**
 * File containing the Content Search handler class
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
 * Visits the Field criterion
 */
abstract class Field extends CriterionVisitor
{
    /**
     * Field map
     *
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldMap
     */
    protected $fieldMap;

    /**
     * Create from content type handler and field registry
     *
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\FieldMap $fieldMap
     *
     * @return void
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
