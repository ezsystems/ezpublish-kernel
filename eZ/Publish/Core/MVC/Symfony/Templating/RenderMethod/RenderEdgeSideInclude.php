<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\RenderMethod;

use eZ\Publish\SPI\MVC\Templating\RenderMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
use Symfony\Component\Routing\RouteCompiler;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 */
final class RenderEdgeSideInclude implements RenderMethod
{
    public const IDENTIFIER = 'esi';

    /** @var \Symfony\Component\HttpKernel\Fragment\AbstractSurrogateFragmentRenderer */
    private $fragmentRenderer;

    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    public function __construct(
        FragmentRendererInterface $fragmentRenderer,
        RouterInterface $router
    ) {
        $this->fragmentRenderer = $fragmentRenderer;
        $this->router = $router;
    }

    public function getIdentifier(): string
    {
        return self::IDENTIFIER;
    }

    public function render(Request $request): string
    {
        $controllerReference = new ControllerReference(
            $request->get('_controller'),
            $this->getRouteAttributes($request)
        );

        $esiFragmentResponse = $this->fragmentRenderer->render($controllerReference, $request);

        return $esiFragmentResponse->getContent();
    }

    private function getRouteAttributes(Request $request): array
    {
        $route = $this->router->getRouteCollection()->get($request->get('_route'));
        $variables = RouteCompiler::compile($route)->getVariables();

        $routeAttributes = [
            'siteaccess' => $request->headers->get('siteaccess'),
        ];

        foreach ($variables as $variable) {
            $routeAttributes[$variable] = $request->attributes->get($variable);
        }

        return $routeAttributes;
    }
}
