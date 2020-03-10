<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class QueryRenderingExtension extends AbstractExtension
{
    /** @var \Symfony\Component\HttpKernel\Fragment\FragmentHandler */
    private $fragmentHandler;

    /** @var string[] */
    private $controllerMap;

    public function __construct(FragmentHandler $fragmentHandler, array $controllerMap)
    {
        $this->fragmentHandler = $fragmentHandler;
        $this->controllerMap = $controllerMap;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ez_render_*_query',
                function (string $type, array $options): ?string {
                    return $this->fragmentHandler->render(
                        $this->createControllerReference($type, $options)
                    );
                },
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'ez_render_*_query_*',
                function (string $type, string $renderer, array $options): ?string {
                    return $this->fragmentHandler->render(
                        $this->createControllerReference($type, $options),
                        $renderer
                    );
                },
                ['is_safe' => ['html']]
            ),
        ];
    }

    private function createControllerReference(string $type, array $options): ControllerReference
    {
        $controller = $this->controllerMap[$type] ?? null;

        if ($controller === null) {
            throw new InvalidArgumentException(
                '$type',
                'Expected value to be of ' . implode(',', array_keys($this->controllerMap))
            );
        }

        return new ControllerReference($controller, [
            'options' => $options,
        ]);
    }
}
