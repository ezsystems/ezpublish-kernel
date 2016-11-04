<?php

/**
 * File containing the BinaryFile Value class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\BinaryFile;

use eZ\Publish\Core\FieldType\BinaryBase\Value as BaseValue;

/**
 * Value for BinaryFile field type.
 */
class Value extends BaseValue
{
    /**
     * Number of times the file has been downloaded through content/download module.
     *
     * @var int
     */
    public $downloadCount = 0;
}
