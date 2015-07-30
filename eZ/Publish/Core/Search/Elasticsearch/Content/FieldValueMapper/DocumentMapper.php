<?php

/**
 * File containing the DocumentMapper document field value mapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\FieldValueMapper;

use eZ\Publish\Core\Search\Elasticsearch\Content\FieldValueMapper;
use eZ\Publish\SPI\Search\FieldType\DocumentField;
use eZ\Publish\SPI\Search\Field;

/**
 * Maps DocumentField document field values to something Elasticsearch can index.
 */
class DocumentMapper extends FieldValueMapper
{
    /**
     * Check if field can be mapped.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return bool
     */
    public function canMap(Field $field)
    {
        return $field->type instanceof DocumentField;
    }

    /**
     * Map field value to a proper Elasticsearch representation.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed
     */
    public function map(Field $field)
    {
        return $field->value;
    }
}
