<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\SPI\Search\FieldType;

/**
 * Generator for search backend field names.
 */
class FieldNameGenerator
{
    /**
     * Simple mapping for our internal field types, consisting of an array
     * of SPI Search FieldType identifier as key and search backend field type
     * string as value.
     *
     * We implement this mapping, because those dynamic fields are common to
     * search backend configurations.
     *
     * @see \eZ\Publish\SPI\Search\FieldType
     *
     * Code example:
     *
     * <code>
     *  array(
     *      "ez_integer" => "i",
     *      "ez_string" => "s",
     *      ...
     *  )
     * </code>
     *
     * @var array
     */
    protected $fieldNameMapping;

    public function __construct(array $fieldNameMapping)
    {
        $this->fieldNameMapping = $fieldNameMapping;
    }

    /**
     * Get name for document field.
     *
     * Consists of a name, and optionally field name and a content type name.
     *
     * @param string $name
     * @param null|string $field
     * @param null|string $type
     *
     * @return string
     */
    public function getName($name, $field = null, $type = null)
    {
        return implode('_', array_filter([$type, $field, $name]));
    }

    /**
     * Map field type.
     *
     * For indexing backend the following scheme will always be used for names:
     * {name}_{type}.
     *
     * Using dynamic fields this allows to define fields either depending on
     * types, or names.
     *
     * Only the field with the name 'id' remains untouched.
     *
     * @param string $name
     * @param \eZ\Publish\SPI\Search\FieldType $type
     *
     * @return string
     */
    public function getTypedName($name, FieldType $type)
    {
        if ($name === 'id') {
            return $name;
        }

        $typeName = $type->type;

        if (isset($this->fieldNameMapping[$typeName])) {
            $typeName = $this->fieldNameMapping[$typeName];
        }

        return $name . '_' . $typeName;
    }
}
