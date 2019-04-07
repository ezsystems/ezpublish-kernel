<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Image;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\FieldType\Nameable;
use eZ\Publish\SPI\FieldType\Value;

class NameableField implements Nameable
{
    /**
     * @param \eZ\Publish\Core\FieldType\Relation\Value $value
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param string $languageCode
     *
     * @return string
     */
    public function getFieldName(Value $value, FieldDefinition $fieldDefinition, $languageCode)
    {
        return !empty($value->alternativeText) ? $value->alternativeText : (string)$value->fileName;
    }
}
