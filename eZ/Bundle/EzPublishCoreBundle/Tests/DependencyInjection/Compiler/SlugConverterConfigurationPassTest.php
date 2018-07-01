<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\SlugConverterConfigurationPass;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class SlugConverterConfigurationPassTest extends AbstractCompilerPassTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SlugConverterConfigurationPass());
    }

    /**
     * @dataProvider configurationProvider
     *
     * @param array $commandsToAdd
     * @param array $existingOldParameters
     * @param array $expectedCommands
     *
     * @throws \ReflectionException
     */
    public function testMergeConfigurations(
        array $commandsToAdd,
        array $existingOldParameters,
        array $expectedCommands
    ) {
        $definition = new Definition(SlugConverter::class);
        $definition->setArgument(0, $this->createMock(TransformationProcessor::class));
        $definition->setArgument(1, $existingOldParameters);

        $this->setDefinition('ezpublish.persistence.slug_converter', $definition);

        $this->setParameter('ezpublish.url_alias.slug_converter', [
            'transformation' => 'urlalias',
            'separator' => 'underscore',
            'transformation_groups' => [
                'urlalias' => [
                    'commands' => $commandsToAdd,
                    'cleanup_method' => 'url_cleanup',
                ],
            ],
        ]);
        $this->compile();

        /** @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter $slugConverter */
        $slugConverterRef = new ReflectionClass(SlugConverter::class);
        $configurationPropertyRef = $slugConverterRef->getProperty('configuration');
        $configurationPropertyRef->setAccessible(true);
        $configuration = $configurationPropertyRef->getValue($this->container->get('ezpublish.persistence.slug_converter'));

        $this->assertEquals('urlalias', $configuration['transformation']);
        $this->assertEquals('underscore', $configuration['wordSeparatorName']);
        $this->assertEquals($expectedCommands, $configuration['transformationGroups']['urlalias']['commands']);
        $this->assertEquals('url_cleanup', $configuration['transformationGroups']['urlalias']['cleanupMethod']);
    }

    public function configurationProvider()
    {
        $injectedBySemanticCommands = [
            'new_command_to_add',
            'second_new_command_to_add',
        ];

        $injectedByParameterCommands = [
            'injected_command',
        ];

        return [
            [
                $injectedBySemanticCommands,
                [],
                array_merge(
                    SlugConverter::DEFAULT_CONFIGURATION['transformationGroups']['urlalias']['commands'],
                    $injectedBySemanticCommands
                ),
            ],
            [
                $injectedBySemanticCommands,
                [
                    'transformation' => 'urlalias_lowercase',
                    'transformationGroups' => [
                        'urlalias' => [
                            'commands' => $injectedByParameterCommands,
                            'cleanupMethod' => 'url_cleanup',
                        ],
                    ],
                    'wordSeparatorName' => 'dash',
                ],
                array_merge(
                    ['injected_command'],
                    $injectedBySemanticCommands
                ),
            ],
        ];
    }
}
