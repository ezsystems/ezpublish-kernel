<?php

/**
 * File containing the ViewControllerListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\MVC\Symfony\Controller\ManagerInterface as ControllerManagerInterface;
use eZ\Publish\Core\MVC\Symfony\View\Configurator;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ViewControllerListener implements EventSubscriberInterface
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
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\Configurator
     */
    private $viewConfigurator;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(
        ControllerResolverInterface $controllerResolver,
        ControllerManagerInterface $controllerManager,
        Repository $repository,
        LoggerInterface $logger,
        Configurator $viewConfigurator,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->controllerManager = $controllerManager;
        $this->controllerResolver = $controllerResolver;
        $this->repository = $repository;
        $this->logger = $logger;
        $this->viewConfigurator = $viewConfigurator;
        $this->authorizationChecker = $authorizationChecker;
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::CONTROLLER => array('getController', 10));
    }

    /**
     * Detects if there is a custom controller to use to render a Location/Content.
     *
     * @param FilterControllerEvent $event
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function getController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        // Only taking content related controller (i.e. ez_content:viewLocation or ez_content:viewContent)
        if (strpos($request->attributes->get('_controller'), 'ez_content:') === false) {
            return;
        }

        $view = new ContentView(null, $request->attributes->get('viewType'));

        if ($request->attributes->has('locationId')) {
            $valueObject = $this->repository->getLocationService()->loadLocation(
                $request->attributes->get('locationId')
            );
            $request->attributes->set('contentId', $valueObject->contentId);
            $request->attributes->set('location', $valueObject);

            $view->setLocation($valueObject);
        } elseif ($request->attributes->get('location') instanceof Location) {
            $valueObject = $request->attributes->get('location');
            $request->attributes->set('locationId', $valueObject->id);
            $request->attributes->set('contentId', $valueObject->contentId);

            $view->setLocation($valueObject);
        }

        if ($request->attributes->has('contentId')) {
            $contentId = $request->attributes->get('contentId');
            $content = $this->repository->sudo(
                function (Repository $repository) use ($contentId) {
                    return $repository->getContentService()->loadContent($contentId);
                }
            );

            $location = ($request->attributes->has('location')) ? ($request->attributes->get('location')) : null;

            if ($view->viewType == 'embed' && !$this->canRead($content, $location)) {
                throw new UnauthorizedException('content', 'read|view_embed', ['contentId' => $contentId]);
            }

            $request->attributes->set('content', $content);
            $view->setContent($content);
        } elseif ($request->attributes->has('content') && $request->attributes->get('content') instanceof Content) {
            $valueObject = $request->attributes->get('content');
            $request->attributes->set('contentId', $valueObject->id);
            $view->setContent($valueObject);
        }

        $this->viewConfigurator->configure($view);
        $request->attributes->set('view', $view);

        $controllerReference = $view->getControllerReference();

        if ($controllerReference instanceof ControllerReference) {
            $request->attributes->set('_controller', $controllerReference->controller);
            $event->setController($this->controllerResolver->getController($request));
        } else {
            if ($request->attributes->get('_controller') === 'ez_content:viewLocation') {
                $request->attributes->set('_controller', 'ez_content:viewContent');
                $event->setController($this->controllerResolver->getController($request));
            }
            if ($request->attributes->get('_controller') === 'ez_content:embedLocation') {
                $request->attributes->set('_controller', 'ez_content:embedContent');
                $event->setController($this->controllerResolver->getController($request));
            }
        }
    }

    /**
     * Checks if a user can read a content, or view it as an embed.
     *
     * @param Content $content
     * @param $location
     *
     * @return bool
     */
    private function canRead(Content $content, Location $location = null)
    {
        $limitations = ['valueObject' => $content->contentInfo];
        if (isset($location)) {
            $limitations['location'] = $location;
        }

        $readAttribute = new AuthorizationAttribute('content', 'read', $limitations);
        $viewEmbedAttribute = new AuthorizationAttribute('content', 'view_embed', $limitations);

        return
            $this->authorizationChecker->isGranted($readAttribute) ||
            $this->authorizationChecker->isGranted($viewEmbedAttribute);
    }
}
