<?php

/**
 * File containing the TranslationCollectorPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\TranslationCollectorPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class TranslationCollectorPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TranslationCollectorPass());
    }

    public function testTranslationCollector(): void
    {
        $this->setDefinition('translator.default', new Definition());
        $this->setParameter('kernel.root_dir', __DIR__ . $this->normalizePath('/../Fixtures/vendor'));

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'translator.default',
            'addResource',
            [
                'xlf',
                __DIR__ . $this->normalizePath('/../Fixtures/vendor/../vendor/ezplatform-i18n/ezplatform-i18n-hi_in/ezpublish-kernel/messages.hi_IN.xlf'),
                'hi',
                'messages',
            ]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'translator.default',
            'addResource',
            [
                'xlf',
                __DIR__ . $this->normalizePath('/../Fixtures/vendor/../vendor/ezplatform-i18n/ezplatform-i18n-nb_no/ezpublish-kernel/messages.nb_NO.xlf'),
                'no',
                'messages',
            ]
        );

        $this->assertContainerBuilderHasParameter('available_translations', ['hi', 'no']);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    private function normalizePath($path)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
