<?php

/**
 * File containing the IOConfigurationPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ComplexSettingsPass;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParser;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\ExpressionLanguage\Expression;

class ComplexSettingsPassTest extends AbstractCompilerPassTestCase
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParserInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $parserMock;

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ComplexSettingsPass(new ComplexSettingParser()));
    }

    public function testProcess()
    {
        $definition = new Definition('stdClass', ['/mnt/nfs/$var_dir$/$storage_dir$']);
        $this->setDefinition('service1', $definition);

        $this->compile();

        $expressionString = 'service("ezpublish.config.complex_setting_value.resolver").resolveSetting("/mnt/nfs/$var_dir$/$storage_dir$", "var_dir", service("ezpublish.config.resolver").getParameter("var_dir", null, null), "storage_dir", service("ezpublish.config.resolver").getParameter("storage_dir", null, null))';
        $arguments = $definition->getArguments();
        self::assertSame(1, count($arguments));
        self::assertInstanceOf(Expression::class, $arguments[0]);
        self::assertSame($expressionString, (string)$arguments[0]);
    }
}
