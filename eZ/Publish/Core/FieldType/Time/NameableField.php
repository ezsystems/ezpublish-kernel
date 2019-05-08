<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Time;

use DateTime;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\FieldType\Nameable;
use eZ\Publish\SPI\FieldType\Value as SPIValue;

class NameableField implements Nameable
{
    /**
     * @param \eZ\Publish\Core\FieldType\Relation\Value $value
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param string $languageCode
     *
     * @return string
     */
    public function getFieldName(SPIValue $value, FieldDefinition $fieldDefinition, $languageCode)
    {
        if ($value === null || $value == new Value()) {
            return '';
        }

        $dateTime = new DateTime("@{$value->time}");

        return $dateTime->format('g:i:s a');
    }
}
