<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\MVC\Templating\RenderMethod;

use eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener;
use eZ\Publish\SPI\MVC\Templating\RenderMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class ControllerRenderMethod implements RenderMethod
{
    /** @var \Symfony\Component\HttpKernel\KernelInterface */
    protected $kernel;

    /** @var \eZ\Bundle\EzPublishCoreBundle\EventListener\ViewControllerListener */
    protected $controllerListener;

    /** @var \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface */
    protected $controllerResolver;

    /** @var \Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface */
    protected $argumentMetadataFactory;

    /** @var \Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface */
    protected $argumentValueResolver;

    public function __construct(
        KernelInterface $kernel,
        ViewControllerListener $controllerListener,
        ControllerResolverInterface $controllerResolver,
        ArgumentMetadataFactoryInterface $argumentMetadataFactory,
        ArgumentValueResolverInterface $argumentValueResolver
    ) {
        $this->kernel = $kernel;
        $this->controllerListener = $controllerListener;
        $this->controllerResolver = $controllerResolver;
        $this->argumentMetadataFactory = $argumentMetadataFactory;
        $this->argumentValueResolver = $argumentValueResolver;
    }

    protected function getControllerEvent(Request $request): ControllerEvent
    {
        $controller = $this->controllerResolver->getController($request);

        $this->controllerListener->getController(
            $event = new ControllerEvent(
                $this->kernel,
                $controller,
                $request,
                HttpKernelInterface::SUB_REQUEST
            )
        );

        return $event;
    }

    protected function getController(ControllerEvent $event): callable
    {
        return $this->controllerResolver->getController($event->getRequest());
    }

    protected function getArguments(callable $controller, ControllerEvent $event): array
    {
        $argumentsMetadata = $this->argumentMetadataFactory->createArgumentMetadata($controller);

        $arguments = [];
        foreach ($argumentsMetadata as $argumentMetadata) {
            // Single-value generator
            $valueGenerator = $this->argumentValueResolver->resolve($event->getRequest(), $argumentMetadata);
            foreach ($valueGenerator as $value) {
                $arguments[$argumentMetadata->getName()] = $value;
                break;
            }
        }

        return $arguments;
    }
}
