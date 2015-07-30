<?php

/**
 * File containing the PageControllerListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\FieldType\Page\PageService;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\MVC\Symfony\Controller\ManagerInterface as ControllerManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PageControllerListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Controller\ManagerInterface
     */
    private $controllerManager;

    /**
     * @var \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface
     */
    private $controllerResolver;

    /**
     * @var \eZ\Publish\Core\FieldType\Page\PageService
     */
    private $pageService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        ControllerResolverInterface $controllerResolver,
        ControllerManagerInterface $controllerManager,
        PageService $pageService,
        LoggerInterface $logger
    ) {
        $this->controllerManager = $controllerManager;
        $this->controllerResolver = $controllerResolver;
        $this->pageService = $pageService;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::CONTROLLER => 'getController');
    }

    /**
     * Detects if there is a custom controller to use to render a Block.
     *
     * @param FilterControllerEvent $event
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function getController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        // Only taking page related controller (i.e. ez_page:viewBlock or ez_page:viewBlockById)
        if (strpos($request->attributes->get('_controller'), 'ez_page:') === false) {
            return;
        }
        try {
            if ($request->attributes->has('id')) {
                $valueObject = $this->pageService->loadBlock(
                    $request->attributes->get('id')
                );
                $request->attributes->set('block', $valueObject);
            } elseif ($request->attributes->get('block') instanceof Block) {
                $valueObject = $request->attributes->get('block');
                $request->attributes->set('id', $valueObject->id);
            }
        } catch (UnauthorizedException $e) {
            throw new AccessDeniedException();
        }

        if (!isset($valueObject)) {
            $this->logger->error('Could not resolve a page controller, invalid value object to match.');

            return;
        }

        $controllerReference = $this->controllerManager->getControllerReference(
            $valueObject,
            'block'
        );

        if (!$controllerReference instanceof ControllerReference) {
            return;
        }

        $request->attributes->set('_controller', $controllerReference->controller);
        $event->setController($this->controllerResolver->getController($request));
    }
}
