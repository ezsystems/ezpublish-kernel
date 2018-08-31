<?php

/**
 * File containing the FieldHelper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Helper;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class FieldHelper
{
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    private $fieldTypeService;

    /**
     * @var TranslationHelper
     */
    private $translationHelper;

    public function __construct(TranslationHelper $translationHelper, ContentTypeService $contentTypeService, FieldTypeService $fieldTypeService)
    {
        $this->fieldTypeService = $fieldTypeService;
        $this->contentTypeService = $contentTypeService;
        $this->translationHelper = $translationHelper;
    }

    /**
     * Checks if provided field can be considered empty.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldDefIdentifier
     * @param null $forcedLanguage
     *
     * @return bool
     */
    public function isFieldEmpty(Content $content, $fieldDefIdentifier, $forcedLanguage = null)
    {
        $field = $this->translationHelper->getTranslatedField($content, $fieldDefIdentifier, $forcedLanguage);
        $fieldDefinition = $content->getContentType()->getFieldDefinition($fieldDefIdentifier);

        return $this
            ->fieldTypeService
            ->getFieldType($fieldDefinition->fieldTypeIdentifier)
            ->isEmptyValue($field->value);
    }

    /**
     * Returns FieldDefinition object based on $contentInfo and $fieldDefIdentifier.
     *
     * @deprecated If you have Content you can instead do: $content->getContentType()->getFieldDefinition($identifier)
     *
     * @param ContentInfo $contentInfo
     * @param string $fieldDefIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public function getFieldDefinition(ContentInfo $contentInfo, $fieldDefIdentifier)
    {
        return $this
            ->contentTypeService
            ->loadContentType($contentInfo->contentTypeId)
            ->getFieldDefinition($fieldDefIdentifier);
    }
}
