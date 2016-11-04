<?php

/**
 * File containing the PathGenerator interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\BinaryBase\PathGenerator;

use eZ\Publish\SPI\FieldType\BinaryBase\PathGenerator;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

class LegacyPathGenerator extends PathGenerator
{
    public function getStoragePathForField(Field $field, VersionInfo $versionInfo)
    {
        $extension = pathinfo($field->value->externalData['fileName'], PATHINFO_EXTENSION);

        return $this->getFirstPartOfMimeType($field->value->externalData['mimeType'])
            . '/' . md5(uniqid(microtime(true), true))
            . (!empty($extension) ? '.' . $extension : '');
    }

    /**
     * Extracts the first part (before the '/') from the given $mimeType.
     *
     * @param string $mimeType
     *
     * @return string
     */
    protected function getFirstPartOfMimeType($mimeType)
    {
        return substr($mimeType, 0, strpos($mimeType, '/'));
    }
}
