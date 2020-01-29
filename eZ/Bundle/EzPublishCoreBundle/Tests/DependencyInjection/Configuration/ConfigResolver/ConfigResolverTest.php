<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\ConfigResolver;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Exception\ParameterNotFoundException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;

abstract class ConfigResolverTest extends TestCase
{
    protected const EXISTING_SA_NAME = 'existing_sa';
    protected const UNDEFINED_SA_NAME = 'undefined_sa';
    protected const SA_GROUP = 'sa_group';

    protected const DEFAULT_NAMESPACE = 'ezsettings';

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    protected $siteAccess;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\DependencyInjection\ContainerInterface */
    protected $containerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siteAccess = new SiteAccess('test');
        $this->containerMock = $this->createMock(ContainerInterface::class);
    }

    abstract protected function getResolver(string $defaultNamespace = 'ezsettings'): ConfigResolverInterface;

    abstract protected function getScope(): string;

    protected function getNamespace(): string
    {
        return self::DEFAULT_NAMESPACE;
    }

    public function testGetParameterFailedWithException(): void
    {
        $resolver = $this->getResolver(self::DEFAULT_NAMESPACE);
        $this->containerMock
            ->expects($this->once())
            ->method('hasParameter')
            ->with(sprintf('%s.%s.undefined', $this->getNamespace(), $this->getScope()))
            ->willReturn(false);

        $this->expectException(ParameterNotFoundException::class);

        $resolver->getParameter('undefined');
    }

    /**
     * @dataProvider parameterProvider
     */
    public function testGetParameterGlobalScope(string $paramName, $expectedValue): void
    {
        $globalScopeParameter = sprintf('%s.%s.%s', $this->getNamespace(), $this->getScope(), $paramName);
        $this->containerMock
            ->expects($this->once())
            ->method('hasParameter')
            ->with($globalScopeParameter)
            ->willReturn(true);
        $this->containerMock
            ->expects($this->once())
            ->method('getParameter')
            ->with($globalScopeParameter)
            ->willReturn($expectedValue);

        $this->assertSame($expectedValue, $this->getResolver()->getParameter($paramName));
    }

    public function parameterProvider(): array
    {
        return [
            ['foo', 'bar'],
            ['some.parameter', true],
            ['some.other.parameter', ['foo', 'bar', 'baz']],
            ['a.hash.parameter', ['foo' => 'bar', 'tata' => 'toto']],
            [
                'a.deep.hash', [
                    'foo' => 'bar',
                    'tata' => 'toto',
                    'deeper_hash' => [
                        'likeStarWars' => true,
                        'jedi' => ['Obi-Wan Kenobi', 'Mace Windu', 'Luke Skywalker', 'Leïa Skywalker (yes! Read episodes 7-8-9!)'],
                        'sith' => ['Darth Vader', 'Darth Maul', 'Palpatine'],
                        'roles' => [
                            'Amidala' => ['Queen'],
                            'Palpatine' => ['Senator', 'Emperor', 'Villain'],
                            'C3PO' => ['Droid', 'Annoying guy'],
                            'Jar-Jar' => ['Still wondering his role', 'Annoying guy'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testGetSetDefaultNamespace(): void
    {
        $newDefaultNamespace = 'new';
        $configResolver = $this->getResolver();
        $this->assertSame(self::DEFAULT_NAMESPACE, $configResolver->getDefaultNamespace());
        $configResolver->setDefaultNamespace($newDefaultNamespace);
        $this->assertSame($newDefaultNamespace, $configResolver->getDefaultNamespace());
    }
}
