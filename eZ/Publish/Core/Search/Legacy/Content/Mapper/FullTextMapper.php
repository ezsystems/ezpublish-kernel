<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Mapper;

use eZ\Publish\Core\Search\Common\FieldRegistry;
use eZ\Publish\Core\Search\Legacy\Content\FullTextData;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\Core\Search\Legacy\Content\FullTextValue;

/**
 * FullTextMapper maps Content object fields to FullTextValue objects which are searchable and
 * therefore can be indexed by the legacy search engine.
 */
class FullTextMapper
{
    /**
     * Field registry.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldRegistry
     */
    protected $fieldRegistry;

    /**
     * Content type handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * @param \eZ\Publish\Core\Search\Common\FieldRegistry $fieldRegistry
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     */
    public function __construct(
        FieldRegistry $fieldRegistry,
        ContentTypeHandler $contentTypeHandler
    ) {
        $this->fieldRegistry = $fieldRegistry;
        $this->contentTypeHandler = $contentTypeHandler;
    }

    /**
     * Map given Content to a FullTextValue.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\Core\Search\Legacy\Content\FullTextData
     */
    public function mapContent(Content $content)
    {
        return new FullTextData(
            [
                'id' => $content->versionInfo->contentInfo->id,
                'contentTypeId' => $content->versionInfo->contentInfo->contentTypeId,
                'sectionId' => $content->versionInfo->contentInfo->sectionId,
                'published' => $content->versionInfo->contentInfo->publicationDate,
                'values' => $this->getFullTextValues($content),
            ]
        );
    }

    /**
     * Returns an array of FullTextValue object containing searchable values of content object
     * fields for the given $content.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\Core\Search\Legacy\Content\FullTextValue[]
     */
    protected function getFullTextValues(Content $content)
    {
        $fullTextValues = [];
        foreach ($content->fields as $field) {
            $fieldDefinition = $this->contentTypeHandler->getFieldDefinition(
                $field->fieldDefinitionId, Content\Type::STATUS_DEFINED
            );
            if (!$fieldDefinition->isSearchable) {
                continue;
            }

            $fieldType = $this->fieldRegistry->getType($field->type);
            $values = $fieldType->getFullTextData($field, $fieldDefinition);
            if (empty($values)) {
                continue;
            }

            $fullTextValues[] = new FullTextValue(
                [
                    'id' => $field->id,
                    'fieldDefinitionId' => $field->fieldDefinitionId,
                    'fieldDefinitionIdentifier' => $fieldDefinition->identifier,
                    'languageCode' => $field->languageCode,
                    'value' => implode(' ', $values),
                ]
            );
        }

        return $fullTextValues;
    }
}
