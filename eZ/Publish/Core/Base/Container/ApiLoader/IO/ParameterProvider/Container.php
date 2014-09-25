<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Base\Container\ApiLoader\IO\ParameterProvider;

use eZ\Publish\Core\Base\Container\ApiLoader\IO\ParameterProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Container implements ParameterProvider
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;
    }

    public function getStorageDir()
    {
        return $this->container->getParameter( 'storage_dir' );
    }

    public function getPublishedImagesPrefix()
    {
        return $this->container->getParameter( 'image_storage_prefix' );
    }

    public function getDraftImagesPrefix()
    {
        return $this->container->getParameter( 'image_draft_storage_prefix' );
    }

    public function getBinaryFilesPrefix()
    {
        return $this->container->getParameter( 'binaryfile_storage_prefix' );
    }
}
