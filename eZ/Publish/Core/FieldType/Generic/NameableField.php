<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\FieldType\Nameable;
use eZ\Publish\SPI\FieldType\Value;
use eZ\Publish\Core\FieldType\Generic\Value as GenericValue;

class NameableField implements Nameable
{
    public function getFieldName(Value $value, FieldDefinition $fieldDefinition, $languageCode): string
    {
        if ($value instanceof GenericValue) {
            return (string)$value;
        }

        return '';
    }
}
