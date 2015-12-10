<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\FieldValueMapper;

use eZ\Publish\Core\Search\Legacy\Content\FieldValueMapper;
use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType;
use DateTime;
use InvalidArgumentException;
use Exception;

/**
 * Maps DateField document field values to something legacy search engine can index.
 */
class DateMapper extends FieldValueMapper
{
    /**
     * Check if field can be mapped.
     *
     * @param Field $field
     *
     * @return mixed
     */
    public function canMap(Field $field)
    {
        return $field->type instanceof FieldType\DateField;
    }

    /**
     * Map field value to a proper legacy search engine representation.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed
     */
    public function map(Field $field)
    {
        if (is_numeric($field->value)) {
            $date = new DateTime("@{$field->value}");
        } else {
            try {
                $date = new DateTime($field->value);
            } catch (Exception $e) {
                throw new InvalidArgumentException('Invalid date provided: ' . $field->value);
            }
        }

        return $date->getTimestamp();
    }
}
