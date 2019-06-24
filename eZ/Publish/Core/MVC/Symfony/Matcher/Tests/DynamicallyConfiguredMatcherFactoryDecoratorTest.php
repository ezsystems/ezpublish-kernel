<?php

/**
 * File containing the ContentBasedMatcherFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\Tests;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;
use eZ\Publish\Core\MVC\Symfony\Matcher\ClassNameMatcherFactory;
use eZ\Publish\Core\MVC\Symfony\Matcher\DynamicallyConfiguredMatcherFactoryDecorator;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use PHPUnit\Framework\TestCase;

class DynamicallyConfiguredMatcherFactoryDecoratorTest extends TestCase
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Matcher\ConfigurableMatcherFactoryInterface */
    private $innerMatcherFactory;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    public function setUp(): void
    {
        $innerMatcherFactory = $this->createMock(ClassNameMatcherFactory::class);
        $configResolver = $this->createMock(ConfigResolver::class);

        $this->innerMatcherFactory = $innerMatcherFactory;
        $this->configResolver = $configResolver;
    }

    /**
     * @dataProvider matchConfigProvider
     */
    public function testMatch($parameterName, $namespace, $scope, $viewsConfiguration, $matchedConfig): void
    {
        $view = $this->createMock(ContentView::class);
        $this->configResolver->expects($this->atLeastOnce())->method('getParameter')->with($parameterName, $namespace,
            $scope)->willReturn($viewsConfiguration);
        $this->innerMatcherFactory->expects($this->once())->method('match')->with($view)->willReturn($matchedConfig);

        $matcherFactory = new DynamicallyConfiguredMatcherFactoryDecorator(
            $this->innerMatcherFactory,
            $this->configResolver,
            $parameterName,
            $namespace,
            $scope
        );

        $this->assertEquals($matchedConfig, $matcherFactory->match($view));
    }

    public function matchConfigProvider(): array
    {
        return [
            [
                'location_view',
                null,
                null,
                [
                    'full' => [
                        'test' => [
                            'template' => 'foo.html.twig',
                            'match' => [
                                \stdClass::class => true,
                            ],
                        ],
                    ],
                ],
                [
                    'template' => 'foo.html.twig',
                    'match' => [
                        \stdClass::class => true,
                    ],
                ],
            ],
        ];
    }
}
