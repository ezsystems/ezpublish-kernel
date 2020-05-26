<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\FieldValueMapper;

use eZ\Publish\SPI\Search\Field;
use eZ\Publish\SPI\Search\FieldType\RemoteIdentifierField;

/**
 * Common remote ID field value mapper.
 *
 * Currently behaves in the same way as StringMapper.
 *
 * @internal for internal use by Search engine field value mapper
 */
class RemoteIdentifierMapper extends StringMapper
{
    public function canMap(Field $field): bool
    {
        return $field->type instanceof RemoteIdentifierField;
    }
}
