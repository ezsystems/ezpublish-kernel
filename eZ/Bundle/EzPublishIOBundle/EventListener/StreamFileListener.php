<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\EventListener;

use eZ\Bundle\EzPublishIOBundle\BinaryStreamResponse;
use eZ\Publish\Core\IO\IOServiceInterface;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listens for IO files requests, and streams them.
 */
class StreamFileListener implements EventSubscriberInterface
{
    /** @var IOServiceInterface */
    private $ioService;

    /** @var ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Bundle\EzPublishIOBundle\EventListener\IoUriMatcher */
    private $uriMatcher;

    public function __construct( IOServiceInterface $ioService, IoUriMatcher $uriMatcher )
    {
        $this->ioService = $ioService;
        $this->uriMatcher = $uriMatcher;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array( 'onKernelRequest', 42 ),
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

        $uri = $event->getRequest()->attributes->get( 'semanticPathinfo' );

        if ( !$this->uriMatcher->matches( $uri ) )
        {
            return;
        }

        // Will throw an API 404 if not found, we can let it pass
        $event->setResponse(
            new BinaryStreamResponse(
                $this->ioService->loadBinaryFileByUri( $uri ),
                $this->ioService
            )
        );
    }
}
