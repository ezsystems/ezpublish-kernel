<?php
/**
 * File containing the ServiceFactory class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Publish\Core\Base\Container\ApiLoader\IO;

use eZ\Publish\Core\IO\IOService;
use eZ\Publish\Core\IO\Handler as IOHandlerInterface;
use eZ\Publish\SPI\IO\MimeTypeDetector;

class ServiceFactory
{
    /**
     * @var MimeTypeDetector
     */
    private $mimeTypeDetector;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct( MimeTypeDetector $mimeTypeDetector, ParameterProvider $parameterProvider )
    {
        $this->mimeTypeDetector = $mimeTypeDetector;
        $this->parameterProvider = $parameterProvider;
    }

    /**
     * Returns a new IOService instance with the config string in $prefixSetting as a prefix
     *
     * @param IoHandlerInterface $IOHandler
     * @param bool|string $prefixSetting
     *
     * @return \eZ\Publish\Core\IO\IOService
     */
    public function buildService( IOHandlerInterface $ioHandler, $prefixSetting = null )
    {
        $settings = array();

        if ( isset( $prefixSetting ) )
        {
            $settings['prefix'] = $this->parameterProvider->getParameter( $prefixSetting );
        }

        return new IOService( $ioHandler, $this->mimeTypeDetector, $settings );
    }
}
