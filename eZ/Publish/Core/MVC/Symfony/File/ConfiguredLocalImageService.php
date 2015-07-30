<?php

/**
 * File containing the ConfiguredLocalImageService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\File;

use eZ\Publish\Core\FieldType\FileService\LegacyFileService as BaseFileService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;

/**
 * Configuration aware local file service for Image FieldType storage.
 */
class ConfiguredLocalImageService extends BaseFileService
{
    public function __construct(\Closure $kernelClosure, ConfigResolverInterface $resolver, $installDir)
    {
        parent::__construct(
            $kernelClosure,
            $installDir,
            '',
            sprintf(
                '%s/%s/images',
                $resolver->getParameter('var_dir'),
                $resolver->getParameter('storage_dir')
            )
        );
    }
}
