<?php

/**
 * File containing the FieldTypeConstraints class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

class FieldTypeConstraints extends ValueObject
{
    /**
     * Validator settings compatible to the corresponding FieldType.
     *
     * This property contains validator settings as defined by the fields type.
     * Note that contents of this property must be serializable and exportable
     * (i.e. no circular references, resources and friends).
     *
     * @see \eZ\Publish\SPI\FieldType\FieldType
     *
     * @var mixed
     */
    public $validators;

    /**
     * Field settings compatible to the corresponding FieldType.
     *
     * This property contains field settings as defined by the fields type.
     * Note that contents of this property must be serializable and exportable
     * (i.e. no circular references, resources and friends).
     *
     * @see \eZ\Publish\SPI\FieldType\FieldType
     *
     * @var mixed
     */
    public $fieldSettings;
}
