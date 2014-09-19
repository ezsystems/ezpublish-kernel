<?php
/**
 * File containing the IOServiceFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Publish\Core\IO\Handler as IoHandlerInterface;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\SPI\IO\MimeTypeDetector;

class IOFactory
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    /** @var string */
    protected $IOServiceClass = 'eZ\\Publish\\Core\\IO\\IOService';

    /** @var MimeTypeDetector */
    protected $mimeTypeDetector;

    /**
     * Constructs a new IOServiceFactory
     *
     * @param ConfigResolverInterface $configResolver
     * @param \eZ\Publish\SPI\IO\MimeTypeDetector $mimeTypeDetector
     */
    public function __construct( ConfigResolverInterface $configResolver, MimeTypeDetector $mimeTypeDetector )
    {
        $this->configResolver = $configResolver;
        $this->mimeTypeDetector = $mimeTypeDetector;
    }

    /**
     * Returns a new IOService instance with the config string in $prefixSetting as a prefix
     *
     * @param IoHandlerInterface $IOHandler
     * @param bool|string $prefixSetting
     *
     * @return \eZ\Publish\Core\IO\IOService
     */
    public function getService( $prefixSetting = false )
    {
        $IOHandler = $this->configResolver->getParameter( 'io_handler' );
        $settings = array();

        if ( $prefixSetting )
        {
            $settings['prefix'] = $this->configResolver->getParameter( $prefixSetting );
        }

        return new $this->IOServiceClass( $IOHandler, $this->mimeTypeDetector, $settings );
    }

    /**
     * Returns an IOHandler instance
     *
     * @param $handlerClass The IOHandler class to instanciate
     * @param string|array $storageDirectorySetting Setting(s) that build-up the storage directory
     *
     * @return mixed
     */
    public function getHandler( $handlerClass, $storageDirectorySetting )
    {
        if ( is_string( $storageDirectorySetting ) )
        {
            $storageDirectory = $this->configResolver->getParameter( $storageDirectorySetting );
        }
        else if ( is_array( $storageDirectorySetting ) )
        {
            $storageDirectoryParts = array();
            foreach ( $storageDirectorySetting as $setting )
            {
                $storageDirectoryParts[] = $this->configResolver->getParameter( $setting );
            }
            $storageDirectory = implode( '/', $storageDirectoryParts );
        }
        return new $handlerClass( $storageDirectory );
    }
}
