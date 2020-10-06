<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\Event\ResolveRenderOptionsEvent;
use eZ\Publish\Core\MVC\Symfony\Templating\RenderOptions;
use eZ\Publish\SPI\MVC\Templating\RenderStrategy;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @internal
 */
final class RenderExtension extends AbstractExtension
{
    /** @var \eZ\Publish\SPI\MVC\Templating\RenderStrategy */
    private $renderStrategy;

    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        RenderStrategy $renderStrategy,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->renderStrategy = $renderStrategy;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ez_render',
                [$this, 'render'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function render(ValueObject $valueObject, array $options = []): string
    {
        if (!$this->renderStrategy->supports($valueObject)) {
            throw new InvalidArgumentException(
                'valueObject',
                sprintf('%s is not supported.', get_class($valueObject))
            );
        }

        $renderOptions = new RenderOptions($options);
        $event = $this->eventDispatcher->dispatch(
            new ResolveRenderOptionsEvent($renderOptions)
        );

        return $this->renderStrategy->render($valueObject, $event->getRenderOptions());
    }
}
