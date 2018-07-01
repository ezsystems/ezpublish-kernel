<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\FieldValueMapper;

use eZ\Publish\Core\Search\Common\FieldValueMapper;
use eZ\Publish\SPI\Search\FieldType\DateField;
use eZ\Publish\SPI\Search\Field;
use DateTime;
use InvalidArgumentException;
use Exception;

/**
 * Common date field value mapper implementation.
 */
class DateMapper extends FieldValueMapper
{
    /**
     * Check if field can be mapped.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed
     */
    public function canMap(Field $field)
    {
        return $field->type instanceof DateField;
    }

    /**
     * Map field value to a proper search engine representation.
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

        return $date->format('Y-m-d\\TH:i:s\\Z');
    }
}
