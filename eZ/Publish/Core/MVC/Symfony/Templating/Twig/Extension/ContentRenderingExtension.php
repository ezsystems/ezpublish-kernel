<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\View\Renderer;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Twig extension for content fields/fieldDefinitions rendering (view and edit).
 */
class ContentRenderingExtension extends Twig_Extension
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener */
    private $controllerListener;

    /** @var \Symfony\Component\HttpKernel\Kernel */
    private $kernel;

    /** @var \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface */
    private $controllerResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Renderer */
    private $viewRenderer;

    /** @var \Twig_Environment */
    private $twig;

    /**
     * ContentRenderingExtension constructor.
     *
     * @param \eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener $controllerListener
     * @param \Symfony\Component\HttpKernel\Kernel $kernel
     * @param \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface $controllerResolver
     */
    public function __construct(
        ViewControllerListener $controllerListener,
        Kernel $kernel,
        ControllerResolverInterface $controllerResolver
    ) {
        $this->controllerListener = $controllerListener;
        $this->kernel = $kernel;
        $this->controllerResolver = $controllerResolver;
    }

    public function getName()
    {
        return 'ezpublish.content_rendering';
    }

    public function initRuntime(Twig_Environment $environment)
    {
        $this->twig = $environment;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'ez_render_content',
                [$this, 'renderContent'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Renders the HTML for a given content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param array $params An array of parameters to pass to the field view
     *
     * @return string The HTML markup
     *
     * @throws InvalidArgumentException
     */
    public function renderContent(Content $content, array $params = [])
    {
        $request = new Request();
        $request->attributes->add([
            '_controller' => 'ez_content:viewAction',
            'contentId' => $content->id,
            'content' => $content,
            'viewType' => 'embed'
        ]);

        $controller = $this->controllerResolver->getController($request);

        if (isset($params['inline']) && $params['inline'] === true) {
            $this->controllerListener->getController(
                $event = new FilterControllerEvent(
                    $this->kernel,
                    $controller,
                    $request,
                    HttpKernelInterface::SUB_REQUEST
                )
            );


            $arguments = $this->controllerResolver->getArguments($request, $event->getController());
            $response = call_user_func_array($event->getController(), $arguments);
            if ($response instanceof View) {
                return $this->twig->render($response->getTemplateIdentifier(), $response->getParameters());
            } elseif ($response instanceof Response) {
                return $response->getContent();
            }
        } else {
            $response = $this->kernel->handle($request);

            return $response->getContent();
        }
    }
}
