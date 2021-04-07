<?php
declare(strict_types=1);

namespace eZ\Publish\SPI\FieldType\BinaryBase;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

interface PathGeneratorInterface
{
    public function getStoragePathForField(Field $field, VersionInfo $versionInfo);
}
