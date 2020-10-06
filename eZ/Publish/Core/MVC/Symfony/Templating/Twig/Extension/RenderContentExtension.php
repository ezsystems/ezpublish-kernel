<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\MVC\Symfony\Event\ResolveRenderOptionsEvent;
use eZ\Publish\Core\MVC\Symfony\Templating\RenderContentStrategy;
use eZ\Publish\Core\MVC\Symfony\Templating\RenderOptions;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @internal
 */
final class RenderContentExtension extends AbstractExtension
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Templating\RenderContentStrategy */
    private $renderContentStrategy;

    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        RenderContentStrategy $renderContentStrategy,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->renderContentStrategy = $renderContentStrategy;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ez_render_content',
                [$this, 'renderContent'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function renderContent(Content $content, array $options = []): string
    {
        $renderOptions = new RenderOptions($options);
        $event = $this->eventDispatcher->dispatch(
            new ResolveRenderOptionsEvent($renderOptions)
        );

        return $this->renderContentStrategy->render($content, $event->getRenderOptions());
    }
}
