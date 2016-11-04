<?php

/**
 * File containing the PathGenerator interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\FieldType\BinaryBase;

use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

abstract class PathGenerator
{
    abstract public function getStoragePathForField(Field $field, VersionInfo $versionInfo);
}
