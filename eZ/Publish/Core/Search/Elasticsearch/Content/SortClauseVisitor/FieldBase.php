<?php

/**
 * File containing the abstract Field sort clause visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\SortClauseVisitor;

use eZ\Publish\Core\Search\Elasticsearch\Content\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\Search\Common\FieldNameResolver;

/**
 * Base class for Field sort clauses.
 */
abstract class FieldBase extends SortClauseVisitor
{
    /**
     * Field map.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldNameResolver
     */
    protected $fieldNameResolver;

    /**
     * @param \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver
     */
    public function __construct(FieldNameResolver $fieldNameResolver)
    {
        $this->fieldNameResolver = $fieldNameResolver;
    }

    /**
     * Get sort field name.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     * @param string $contentTypeIdentifier
     * @param string $fieldDefinitionIdentifier
     * @param string $name
     *
     * @return array
     */
    protected function getSortFieldName(
        SortClause $sortClause,
        $contentTypeIdentifier,
        $fieldDefinitionIdentifier,
        $name = null
    ) {
        return $this->fieldNameResolver->getSortFieldName(
            $sortClause,
            $contentTypeIdentifier,
            $fieldDefinitionIdentifier,
            $name
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
    protected function getNestedFilterTerm($languageCode)
    {
        if ($languageCode === null) {
            return [
                'fields_doc.meta_is_main_translation_b' => true,
            ];
        }

        return [
            'fields_doc.meta_language_code_s' => $languageCode,
        ];
    }
}
