<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RegisterSearchEnginePass;
use LogicException;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterSearchEnginePassTest extends AbstractCompilerPassTestCase
{
    private const EXAMPLE_SERVICE_ID = 'app.search_engine';
    private const EXAMPLE_ALIAS = 'foo';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setDefinition('ezpublish.api.search_engine.factory', new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterSearchEnginePass());
    }

    /**
     * @dataProvider tagsProvider
     */
    public function testRegisterSearchEngine(string $tag): void
    {
        $definition = new Definition();
        $definition->addTag($tag, [
            'alias' => self::EXAMPLE_ALIAS,
        ]);

        $this->setDefinition(self::EXAMPLE_SERVICE_ID, $definition);
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.search_engine.factory',
            'registerSearchEngine',
            [
                new Reference(self::EXAMPLE_SERVICE_ID),
                self::EXAMPLE_ALIAS,
            ]
        );
    }

    /**
     * @dataProvider tagsProvider
     */
    public function testRegisterSearchEngineWithoutAliasThrowsLogicException(string $tag): void
    {
        $this->expectException(LogicException::class);

        $definition = new Definition();
        $definition->addTag($tag);

        $this->setDefinition(self::EXAMPLE_SERVICE_ID, $definition);
        $this->compile();
    }

    public function tagsProvider(): iterable
    {
        return [
            [RegisterSearchEnginePass::SEARCH_ENGINE_SERVICE_TAG],
            [RegisterSearchEnginePass::DEPRECATED_SEATCH_ENGINE_SERVICE_TAG],
        ];
    }
}
