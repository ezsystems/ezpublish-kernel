<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\Tests\EzPlatformTemplating\EventListener;

use ArrayIterator;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Event\PreContentViewEvent;
use eZ\Publish\Core\MVC\Symfony\EventListener\ContentViewSubscriber;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use eZ\Publish\Core\MVC\Symfony\View\GenericVariableProviderRegistry;
use eZ\Publish\Core\MVC\Symfony\View\VariableProvider;
use eZ\Publish\Core\MVC\Symfony\View\View;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;

final class ContentViewSubscriberTest extends TestCase
{
    /**
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getContentViewMock(): ContentView
    {
        $view = $this->createMock(ContentView::class);

        $view->method('getContent')->willReturn(new Content());
        $view->method('getLocation')->willReturn(new Location());

        return $view;
    }

    private function getRegistry(array $providers): GenericVariableProviderRegistry
    {
        return new GenericVariableProviderRegistry(
            new ArrayIterator($providers)
        );
    }

    private function getProvider(string $identifier): VariableProvider
    {
        return new class($identifier) implements VariableProvider {
            private $identifier;

            public function __construct(string $identifier)
            {
                $this->identifier = $identifier;
            }

            public function getIdentifier(): string
            {
                return $this->identifier;
            }

            public function getTwigVariables(View $view, array $options = []): object
            {
                return (object)[
                    $this->identifier . '_parameter' => $this->identifier . '_value',
                ];
            }
        };
    }

    private function getContentViewMockSubscriber(): ContentViewSubscriber
    {
        return new ContentViewSubscriber(
            $this->getRegistry([
                $this->getProvider('test_provider'),
            ]),
            $this->createMock(ConfigResolverInterface::class)
        );
    }

    public function testWithoutVariables(): void
    {
        $view = $this->getContentViewMock();
        $event = new PreContentViewEvent($view);

        $view
            ->expects($this->once())
            ->method('setParameters')
            ->with([]);

        $subscriber = $this->getContentViewMockSubscriber();
        $subscriber->onPreContentView($event);
    }

    public function testWithScalarVariables(): void
    {
        $view = $this->getContentViewMock();
        $event = new PreContentViewEvent($view);

        $view->method('getConfigHash')
            ->willReturn([
                ContentViewSubscriber::PARAMETERS_KEY => [
                    'param_1' => 'scalar_1',
                    'param_2' => 2,
                    'param_3' => 3,
                ],
            ]);

        $view
            ->expects($this->once())
            ->method('setParameters')
            ->with([
                'param_1' => 'scalar_1',
                'param_2' => 2,
                'param_3' => 3,
            ]);

        $subscriber = $this->getContentViewMockSubscriber();
        $subscriber->onPreContentView($event);
    }

    public function testOverwriteVariables(): void
    {
        $view = $this->getContentViewMock();
        $event = new PreContentViewEvent($view);

        $view->method('getConfigHash')
            ->willReturn([
                ContentViewSubscriber::PARAMETERS_KEY => [
                    'param_1' => 'scalar_1',
                ],
            ]);

        $view->method('getParameters')
            ->willReturn([
                'param_1' => 'existing_value',
                'param_2' => 'also_existing_value',
            ]);

        $view
            ->expects($this->once())
            ->method('setParameters')
            ->with([
                'param_1' => 'scalar_1',
                'param_2' => 'also_existing_value',
            ]);

        $subscriber = $this->getContentViewMockSubscriber();
        $subscriber->onPreContentView($event);
    }

    public function testWithExpressionParam(): void
    {
        $view = $this->getContentViewMock();
        $event = new PreContentViewEvent($view);

        $randomNumber = rand(100, 200);

        $view->method('getParameters')
            ->willReturn([
                'random_number' => $randomNumber,
            ]);

        $view->method('getConfigHash')
            ->willReturn([
                ContentViewSubscriber::PARAMETERS_KEY => [
                    'plus_42' => '@=parameters["random_number"] + 42',
                ],
            ]);

        $view
            ->expects($this->once())
            ->method('setParameters')
            ->with([
                'random_number' => $randomNumber,
                'plus_42' => $randomNumber + 42,
            ]);

        $subscriber = $this->getContentViewMockSubscriber();
        $subscriber->onPreContentView($event);
    }

    public function testWithNestedParamsAndExpressions(): void
    {
        $view = $this->getContentViewMock();
        $event = new PreContentViewEvent($view);

        $someNumber = 123;

        $view->method('getParameters')
            ->willReturn([
                'some_number' => $someNumber,
            ]);

        $view->method('getConfigHash')
            ->willReturn([
                ContentViewSubscriber::PARAMETERS_KEY => [
                    'example' => [
                        'plus_42' => '@=parameters["some_number"] + 42',
                        'nested' => [
                            'some' => 'variable',
                            'minus_42' => '@=parameters["some_number"] - 42',
                        ],
                    ],
                ],
            ]);

        $view
            ->expects($this->once())
            ->method('setParameters')
            ->with([
                'some_number' => $someNumber,
                'example' => [
                    'plus_42' => $someNumber + 42,
                    'nested' => [
                        'some' => 'variable',
                        'minus_42' => $someNumber - 42,
                    ],
                ],
            ]);

        $subscriber = $this->getContentViewMockSubscriber();
        $subscriber->onPreContentView($event);
    }

    public function testWithProviderExpression()
    {
        $view = $this->getContentViewMock();
        $event = new PreContentViewEvent($view);

        $view->method('getParameters')->willReturn([]);

        $view->method('getConfigHash')
            ->willReturn([
                ContentViewSubscriber::PARAMETERS_KEY => [
                    'example' => '@=provider("test_provider").test_provider_parameter',
                ],
            ]);

        $view
            ->expects($this->once())
            ->method('setParameters')
            ->with([
                'example' => 'test_provider_value',
            ]);

        $subscriber = $this->getContentViewMockSubscriber();
        $subscriber->onPreContentView($event);
    }
}
