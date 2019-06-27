<?php

/**
 * File containing the ContentExtension class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\Helper\FieldHelper;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use Psr\Log\LoggerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig content extension for eZ Publish specific usage.
 * Exposes helpers to play with public API objects.
 */
class ContentExtension extends AbstractExtension
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\Core\Helper\TranslationHelper */
    protected $translationHelper;

    /** @var \eZ\Publish\Core\Helper\FieldHelper */
    protected $fieldHelper;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        Repository $repository,
        TranslationHelper $translationHelper,
        FieldHelper $fieldHelper,
        LoggerInterface $logger = null
    ) {
        $this->repository = $repository;
        $this->translationHelper = $translationHelper;
        $this->fieldHelper = $fieldHelper;
        $this->logger = $logger;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'ez_content_name',
                [$this, 'getTranslatedContentName']
            ),
            new TwigFunction(
                'ez_field_value',
                [$this, 'getTranslatedFieldValue']
            ),
            new TwigFunction(
                'ez_field',
                [$this, 'getTranslatedField']
            ),
            new TwigFunction(
                'ez_field_is_empty',
                [$this, 'isFieldEmpty']
            ),
            new TwigFunction(
                'ez_field_name',
                [$this, 'getTranslatedFieldDefinitionName']
            ),
            new TwigFunction(
                'ez_field_description',
                [$this, 'getTranslatedFieldDefinitionDescription']
            ),
            new TwigFunction(
                'ez_content_field_identifier_first_filled_image',
                [$this, 'getFirstFilledImageFieldIdentifier']
            ),
        ];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'ezpublish.content';
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ValueObject $content Must be a valid Content or ContentInfo object.
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType When $content is not a valid Content or ContentInfo object.
     *
     * @return string
     */
    public function getTranslatedContentName(ValueObject $content, $forcedLanguage = null)
    {
        if ($content instanceof Content) {
            return $this->translationHelper->getTranslatedContentName($content, $forcedLanguage);
        } elseif ($content instanceof ContentInfo) {
            return $this->translationHelper->getTranslatedContentNameByContentInfo($content, $forcedLanguage);
        }

        throw new InvalidArgumentType('$content', 'eZ\Publish\API\Repository\Values\Content\Content or eZ\Publish\API\Repository\Values\Content\ContentInfo', $content);
    }

    /**
     * Returns the translated field, very similar to getTranslatedFieldValue but this returns the whole field.
     * To be used with ez_image_alias for example, which requires the whole field.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldDefIdentifier Identifier for the field we want to get.
     * @param string $forcedLanguage Locale we want the field in (e.g. "cro-HR"). Null by default (takes current locale).
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field
     */
    public function getTranslatedField(Content $content, $fieldDefIdentifier, $forcedLanguage = null)
    {
        return $this->translationHelper->getTranslatedField($content, $fieldDefIdentifier, $forcedLanguage);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldDefIdentifier Identifier for the field we want to get the value from.
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale).
     *
     * @return mixed A primitive type or a field type Value object depending on the field type.
     */
    public function getTranslatedFieldValue(Content $content, $fieldDefIdentifier, $forcedLanguage = null)
    {
        return $this->translationHelper->getTranslatedField($content, $fieldDefIdentifier, $forcedLanguage)->value;
    }

    /**
     * Gets name of a FieldDefinition name by loading ContentType based on Content/ContentInfo object.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $content Must be Content or ContentInfo object
     * @param string $fieldDefIdentifier Identifier for the field we want to get the name from
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType When $content is not a valid Content object.
     *
     * @return string|null
     */
    public function getTranslatedFieldDefinitionName(ValueObject $content, $fieldDefIdentifier, $forcedLanguage = null)
    {
        if ($contentType = $this->getContentType($content)) {
            return $this->translationHelper->getTranslatedFieldDefinitionProperty(
                $contentType,
                $fieldDefIdentifier,
                'name',
                $forcedLanguage
            );
        }

        throw new InvalidArgumentType('$content', 'Content|ContentInfo', $content);
    }

    /**
     * Gets name of a FieldDefinition description by loading ContentType based on Content/ContentInfo object.
     *
     * @param \eZ\Publish\API\Repository\Values\ValueObject $content Must be Content or ContentInfo object
     * @param string $fieldDefIdentifier Identifier for the field we want to get the name from
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR"). Null by default (takes current locale)
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType When $content is not a valid Content object.
     *
     * @return string|null
     */
    public function getTranslatedFieldDefinitionDescription(ValueObject $content, $fieldDefIdentifier, $forcedLanguage = null)
    {
        if ($contentType = $this->getContentType($content)) {
            return $this->translationHelper->getTranslatedFieldDefinitionProperty(
                $contentType,
                $fieldDefIdentifier,
                'description',
                $forcedLanguage
            );
        }

        throw new InvalidArgumentType('$content', 'Content|ContentInfo', $content);
    }

    /**
     * Checks if a given field is considered empty.
     * This method accepts field as Objects or by identifiers.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Field|string $fieldDefIdentifier Field or Field Identifier to
     *                                                                                   get the value from.
     * @param string $forcedLanguage Locale we want the content name translation in (e.g. "fre-FR").
     *                               Null by default (takes current locale).
     *
     * @return bool
     */
    public function isFieldEmpty(Content $content, $fieldDefIdentifier, $forcedLanguage = null)
    {
        if ($fieldDefIdentifier instanceof Field) {
            $fieldDefIdentifier = $fieldDefIdentifier->fieldDefIdentifier;
        }

        return $this->fieldHelper->isFieldEmpty($content, $fieldDefIdentifier, $forcedLanguage);
    }

    /**
     * Get ContentType by Content/ContentInfo.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content|\eZ\Publish\API\Repository\Values\Content\ContentInfo $content
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType|null
     */
    private function getContentType(ValueObject $content)
    {
        if ($content instanceof Content) {
            return $this->repository->getContentTypeService()->loadContentType(
                $content->getVersionInfo()->getContentInfo()->contentTypeId
            );
        } elseif ($content instanceof ContentInfo) {
            return $this->repository->getContentTypeService()->loadContentType($content->contentTypeId);
        }
    }

    public function getFirstFilledImageFieldIdentifier(Content $content)
    {
        foreach ($content->getFieldsByLanguage() as $field) {
            $fieldTypeIdentifier = $content->getContentType()
                ->getFieldDefinition($field->fieldDefIdentifier)
                ->fieldTypeIdentifier;

            if ($fieldTypeIdentifier !== 'ezimage') {
                continue;
            }

            if ($this->fieldHelper->isFieldEmpty($content, $field->fieldDefIdentifier)) {
                continue;
            }

            return $field->fieldDefIdentifier;
        }

        return null;
    }
}
