<?php

/**
 * File containing the NameableField class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Selection;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\FieldType\Nameable;
use eZ\Publish\SPI\FieldType\Value as SPIValue;

/**
 * Class NameableField for Selection FieldType.
 */
class NameableField implements Nameable
{
    /**
     * @param \eZ\Publish\Core\FieldType\Selection\Value $value
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param string $languageCode
     *
     * @return string
     */
    public function getFieldName(SPIValue $value, FieldDefinition $fieldDefinition, $languageCode)
    {
        if (empty($value->selection)) {
            return '';
        }

        $names = [];
        $fieldSettings = $fieldDefinition->getFieldSettings();

        foreach ($value->selection as $optionIndex) {
            if (isset($fieldSettings['multilingualOptions'][$languageCode][$optionIndex])) {
                $names[] = $fieldSettings['multilingualOptions'][$languageCode][$optionIndex];
            } elseif (isset($fieldSettings['multilingualOptions'][$fieldDefinition->mainLanguageCode][$optionIndex])) {
                $names[] = $fieldSettings['multilingualOptions'][$fieldDefinition->mainLanguageCode][$optionIndex];
            } elseif (isset($fieldSettings['options'][$optionIndex])) {
                $names[] = $fieldSettings['options'][$optionIndex];
            }
        }

        return implode(' ', $names);
    }
}
