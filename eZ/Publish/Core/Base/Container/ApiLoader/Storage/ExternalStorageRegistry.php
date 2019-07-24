<?php

/**
 * File containing the ExternalStorageRegistryFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\ApiLoader\Storage;

class ExternalStorageRegistry
{
    /**
     * Collection of external storage handlers for field types that need them.
     *
     * @var \eZ\Publish\SPI\FieldType\FieldStorage[]
     */
    protected $externalStorages;

    /**
     * @param \eZ\Publish\SPI\FieldType\FieldStorage[] $externalStorages
     */
    public function __construct(array $externalStorages)
    {
        $this->externalStorages = $externalStorages;
    }

    public function registerExternalStorageHandler(string $identifier, $externalStorage): void
    {
        $this->externalStorages[$identifier] = $externalStorage;
    }

    /**
     * @return \eZ\Publish\SPI\FieldType\FieldStorage[]
     */
    public function getExternalStorageHandlers(): array
    {
        return $this->externalStorages;
    }
}
