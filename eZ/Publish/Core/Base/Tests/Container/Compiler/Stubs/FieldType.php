<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Stubs;

use eZ\Publish\Core\FieldType\FieldType as CoreFieldType;
use eZ\Publish\Core\FieldType\Value as CoreFieldTypeValue;
use eZ\Publish\SPI\FieldType\Value;

class FieldType extends CoreFieldType
{
    protected function createValueFromInput($inputValue)
    {
    }

    public function getFieldTypeIdentifier()
    {
    }

    public function getName(Value $value)
    {
    }

    public function getEmptyValue()
    {
    }

    public function fromHash($hash)
    {
    }

    public function toHash(Value $value)
    {
    }

    protected function checkValueStructure(CoreFieldTypeValue $value): void
    {
    }
}
