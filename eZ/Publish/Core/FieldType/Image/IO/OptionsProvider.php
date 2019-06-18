<?php

/**
 * This file is part of the eZ Publish Legacy package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Image\IO;

class OptionsProvider
{
    protected $varDir;

    protected $storageDir;

    protected $draftImagesDir;

    protected $publishedImagesDir;

    public function __construct(array $values = [])
    {
        $this->varDir = isset($values['var_dir']) ? $values['var_dir'] : null;
        $this->storageDir = isset($values['storage_dir']) ? $values['storage_dir'] : null;
        $this->draftImagesDir = isset($values['draft_images_dir']) ? $values['draft_images_dir'] : null;
        $this->publishedImagesDir = isset($values['published_images_dir']) ? $values['published_images_dir'] : null;
    }

    public function setVarDir($varDir)
    {
        $this->varDir = $varDir;
    }

    public function getVarDir()
    {
        return $this->varDir;
    }

    public function setStorageDir($storageDir)
    {
        $this->storageDir = $storageDir;
    }

    public function getStorageDir()
    {
        return $this->storageDir;
    }

    public function setDraftImagesDir($draftImagesDir)
    {
        $this->draftImagesDir = $draftImagesDir;
    }

    public function getDraftImagesDir()
    {
        return $this->draftImagesDir;
    }

    public function setPublishedImagesDir($publishedImagesDir)
    {
        $this->publishedImagesDir = $publishedImagesDir;
    }

    public function getPublishedImagesDir()
    {
        return $this->publishedImagesDir;
    }
}
