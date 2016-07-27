<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\FieldType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

/**
 * The field type interface which all field types have to implement to be
 * able to generate content name when field is part of name-schema or url-schema.
 *
 * For use by business logic (API implementation), so can *not* directly or indirectly rely on API.
 *
 * Most FieldTypes will only need to use Value object in order to generate name, however others will
 * for instance need to use other services. Psudo-example for a Content Relation FieldType:
 * ```php
 * class NameField implements Nameable
 * {
 *     public function __construct(ContentHandler $contentHandler){...}
 *
 *     publish function getFieldName(Value $value, FieldDefinition $fieldDefinition, $languageCode)
 *     {
 *         // This will only return main language but gives an example of use
 *         return $this->contentHandler->loadContentInfo($value->destinationContentId)->name;
 *     }
 * }
 * ```
 *
 *
 * @since 6.3/5.4.7
 */
interface Nameable
{
    /**
     * Returns a human readable string representation from a given field.
     *
     * It will be used to generate content name and url alias if current field
     * is designated to be used in the content name/urlAlias pattern.
     *
     * The used $value can be assumed to be already accepted by {@link FieldType::acceptValue()}.
     *
     * @param \eZ\Publish\SPI\FieldType\Value $value
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param string $languageCode
     *
     * @return string
     */
    public function getFieldName(Value $value, FieldDefinition $fieldDefinition, $languageCode);
}
