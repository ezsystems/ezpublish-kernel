<?php
/**
 * File containing the abstract Field sort clause visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Target\FieldTarget;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldMap;

/**
 * Base class for Field sort clauses
 */
abstract class FieldBase extends SortClauseVisitor
{
    /**
     * Field map
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldMap
     */
    protected $fieldMap;

    /**
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldMap $fieldMap
     */
    public function __construct( FieldMap $fieldMap )
    {
        $this->fieldMap = $fieldMap;
    }

    /**
     * Get field type information
     *
     * @param string $contentTypeIdentifier
     * @param string $fieldDefinitionIdentifier
     * @param string $languageCode
     *
     * @return array
     */
    protected function getFieldTypes( $contentTypeIdentifier, $fieldDefinitionIdentifier, $languageCode )
    {
        return $this->fieldMap->getSortFieldTypes(
            $contentTypeIdentifier,
            $fieldDefinitionIdentifier,
            $languageCode
        );
    }

    /**
     * Returns the term condition for nested filter, used to target specific nested field document.
     *
     * If given $languageCode is not null the condition targets the field document in the
     * given translation, otherwise document for translation in main language code is returned.
     *
     * @param null|string $languageCode
     *
     * @return mixed
     */
    protected function getNestedFilterTerm( $languageCode )
    {
        if ( $languageCode === null )
        {
            return array(
                "fields_doc.meta_is_main_translation_b" => true,
            );
        }

        return array(
            "fields_doc.meta_language_code_s" => $languageCode,
        );
    }
}
