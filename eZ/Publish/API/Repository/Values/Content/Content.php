<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;

/**
 * this class represents a content object in a specific version.
 *
 * @property-read \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo convenience getter for getVersionInfo()->getContentInfo()
 * @property-read int $id convenience getter for retrieving the contentId: $versionInfo->contentInfo->id
 * @property-read \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo calls getVersionInfo()
 * @property-read \eZ\Publish\API\Repository\Values\Content\Field[] $fields access fields, calls getFields()
 * @property-read \eZ\Publish\API\Repository\Values\Content\Thumbnail|null $thumbnail calls getThumbnail()
 */
abstract class Content extends ValueObject
{
    /**
     * Returns the VersionInfo for this version.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    abstract public function getVersionInfo(): VersionInfo;

    /**
     * Shorthand method for getVersionInfo()->getName().
     *
     * @see \eZ\Publish\API\Repository\Values\Content\VersionInfo::getName()
     *
     * @param string|null $languageCode
     *
     * @return string|null The name for a given language, or null if $languageCode is not set
     *         or does not exist.
     */
    public function getName(?string $languageCode = null): ?string
    {
        return $this->getVersionInfo()->getName($languageCode);
    }

    /**
     * Returns a field value for the given value.
     *
     * - If $languageCode is defined,
     *      return if available, otherwise null
     * - If not pick using the following languages codes when applicable:
     *      1. Prioritized languages (if provided to api on object retrieval)
     *      2. Main language
     *
     * On non translatable fields this method ignores the languageCode parameter, and return main language field value.
     *
     * @param string $fieldDefIdentifier
     * @param string|null $languageCode
     *
     * @return \eZ\Publish\SPI\FieldType\Value|null a primitive type or a field type Value object depending on the field type.
     */
    abstract public function getFieldValue(string $fieldDefIdentifier, ?string $languageCode = null): ?\eZ\Publish\SPI\FieldType\Value;

    /**
     * This method returns the complete fields collection.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[] An array of {@link Field}
     */
    abstract public function getFields(): iterable;

    /**
     * This method returns the fields for a given language and non translatable fields.
     *
     * - If $languageCode is defined, return if available
     * - If not pick using prioritized languages (if provided to api on object retrieval)
     * - Otherwise return in main language
     *
     * @param string|null $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field[] An array of {@link Field} with field identifier as keys
     */
    abstract public function getFieldsByLanguage(?string $languageCode = null): iterable;

    /**
     * This method returns the field for a given field definition identifier and language.
     *
     * - If $languageCode is defined,
     *      return if available, otherwise null
     * - If not pick using the following languages codes when applicable:
     *      1. Prioritized languages (if provided to api on object retrieval)
     *      2. Main language
     *
     * On non translatable fields this method ignores the languageCode parameter, and return main language field.
     *
     * @param string $fieldDefIdentifier
     * @param string|null $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Field|null A {@link Field} or null if nothing is found
     */
    abstract public function getField(string $fieldDefIdentifier, ?string $languageCode = null): ?Field;

    /**
     * Returns the ContentType for this content.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    abstract public function getContentType(): ContentType;

    abstract public function getThumbnail(): ?Thumbnail;
}
