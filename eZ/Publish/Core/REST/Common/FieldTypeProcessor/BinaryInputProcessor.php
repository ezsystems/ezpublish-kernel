<?php

/**
 * File containing the BinaryInputProcessor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor;

abstract class BinaryInputProcessor extends FieldTypeProcessor
{
    /** @var string */
    protected $temporaryDirectory;

    /**
     * @param string $temporaryDirectory
     */
    public function __construct($temporaryDirectory)
    {
        $this->temporaryDirectory = $temporaryDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function preProcessValueHash($incomingValueHash)
    {
        if (isset($incomingValueHash['data'])) {
            $tempFile = tempnam($this->temporaryDirectory, 'eZ_REST_BinaryFile');

            file_put_contents(
                $tempFile,
                base64_decode($incomingValueHash['data'])
            );

            unset($incomingValueHash['data']);
            $incomingValueHash['inputUri'] = $tempFile;

            register_shutdown_function('unlink', $tempFile);
        }

        return $incomingValueHash;
    }
}
