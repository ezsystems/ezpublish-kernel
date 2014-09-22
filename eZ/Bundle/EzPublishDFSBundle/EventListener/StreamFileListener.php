<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishDFSBundle\EventListener;

use eZ\Publish\Core\IO\IOServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class StreamFileListener implements EventSubscriberInterface
{

    /**
     * @var IOServiceInterface
     */
    private $ioService;

    public function __construct( IOServiceInterface $ioService )
    {
        $this->ioService = $ioService;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array( 'onKernelRequest', -1000 ),
        );
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest( GetResponseEvent $event )
    {
        if ( $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST )
        {
            return;
        }

        $request = $event->getRequest();

        $path = $request->attributes->get( 'semanticPathinfo' );
        if ( !$this->isIOFile( $path ) )
        {
            return;
        }

        $response = new BinaryFileResponse( $this->getLocalFilePath( $path ) );
        $response->headers->set('Content-Type', 'image/png');
        $event->setResponse( $response );
    }

    private function isIOFile( $path )
    {
        return $this->ioService->exists( ltrim( $path, '/' ) );
    }

    /**
     * @param $path
     *
     * @return string
     */
    protected function getLocalFilePath( $path )
    {
        return '/home/bertrand/nfs' . $path;
    }
}
