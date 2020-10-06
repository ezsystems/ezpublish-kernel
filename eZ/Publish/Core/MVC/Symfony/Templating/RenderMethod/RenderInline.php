<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\RenderMethod;

use eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener;
use eZ\Publish\Core\MVC\Symfony\Templating\Exception\InvalidResponseException;
use eZ\Publish\Core\MVC\Symfony\View\Renderer\TemplateRenderer;
use eZ\Publish\Core\MVC\Symfony\View\View;
use eZ\Publish\SPI\MVC\Templating\RenderMethod;
use eZ\Publish\SPI\MVC\Templating\RenderMethod\ControllerRenderMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @internal
 */
final class RenderInline extends ControllerRenderMethod implements RenderMethod
{
    public const IDENTIFIER = 'inline';

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Renderer\TemplateRenderer */
    private $viewTemplateRenderer;

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    public function __construct(
        KernelInterface $kernel,
        ViewControllerListener $controllerListener,
        ControllerResolverInterface $controllerResolver,
        ArgumentMetadataFactoryInterface $argumentMetadataFactory,
        ArgumentValueResolverInterface $argumentValueResolver,
        TemplateRenderer $viewTemplateRenderer
    ) {
        parent::__construct(
            $kernel,
            $controllerListener,
            $controllerResolver,
            $argumentMetadataFactory,
            $argumentValueResolver
        );

        $this->viewTemplateRenderer = $viewTemplateRenderer;
    }

    public function render(Request $request): string
    {
        $event = $this->getControllerEvent($request);
        $controller = $this->getController($event);
        $arguments = $this->getArguments($controller, $event);

        $response = call_user_func_array($controller, $arguments);

        if ($response instanceof View) {
            return $this->viewTemplateRenderer->render($response);
        } elseif ($response instanceof Response) {
            return $response->getContent();
        } elseif (is_string($response)) {
            return $response;
        }

        throw new InvalidResponseException(
            sprintf('Unsupported type (%s)', get_class($response))
        );
    }
}
