<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\FieldValueMapper;

use eZ\Publish\Core\Search\Common\FieldValueMapper\FloatMapper as CommonFloatMapper;
use eZ\Publish\SPI\Search\Field;

/**
 * Maps FloatField document field values to something legacy search engine can index.
 */
class FloatMapper extends CommonFloatMapper
{
    /**
     * Map field value to a proper legacy search engine representation.
     *
     * @param \eZ\Publish\SPI\Search\Field $field
     *
     * @return mixed
     */
    public function map(Field $field)
    {
        return (float) $field->value;
    }
}
