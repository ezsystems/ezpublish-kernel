<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\ConfigResolver;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ChainConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver\DefaultScopeConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver\GlobalScopeConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver\SiteAccessGroupConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver\StaticSiteAccessConfigResolver;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Provider\StaticSiteAccessProvider;
use eZ\Publish\Core\MVC\Symfony\SiteAccessGroup;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ChainConfigResolverTest extends TestCase
{
    private const FIRST_SA_NAME = 'first_sa';
    private const SECOND_SA_NAME = 'second_sa';
    private const SA_GROUP = 'sa_group';

    private const DEFAULT_NAMESPACE = 'ezsettings';

    private const SCOPE_DEFAULT = 'default';
    private const SCOPE_GLOBAL = 'global';

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess|\PHPUnit\Framework\MockObject\MockObject */
    private $siteAccess;

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $containerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siteAccess = new SiteAccess(self::FIRST_SA_NAME);
        $this->siteAccess->groups = [new SiteAccessGroup(self::SA_GROUP)];
        $this->containerMock = $this->createMock(ContainerInterface::class);
    }

    /**
     * @dataProvider parameterProvider
     */
    public function testGetParameterDefaultScope(string $paramName, $expectedValue): void
    {
        $globalScopeParameter = $this->getParameter($paramName, self::SCOPE_GLOBAL);
        $relativeScopeParameter = $this->getParameter($paramName, $this->siteAccess->name);
        $saGroupScopeParameter = $this->getParameter($paramName, self::SA_GROUP);
        $defaultScopeParameter = $this->getParameter($paramName, self::SCOPE_DEFAULT);
        $this->containerMock
             ->expects($this->exactly(4))
             ->method('hasParameter')
             ->with(
                 $this->logicalOr(
                     $globalScopeParameter,
                     $relativeScopeParameter,
                     $saGroupScopeParameter,
                     $defaultScopeParameter
                 )
             )
             // First call is for "global" scope, second for SA scope, third fo SA group scope, last is the right one
             ->will($this->onConsecutiveCalls(false, false, false, true));
        $this->containerMock
             ->expects($this->once())
             ->method('getParameter')
             ->with($defaultScopeParameter)
             ->willReturn($expectedValue);

        $this->assertSame($expectedValue, $this->getChainConfigResolver()->getParameter($paramName));
    }

    /**
     * @dataProvider parameterProvider
     */
    public function testGetParameterRelativeScope(string $paramName, $expectedValue): void
    {
        $globalScopeParameter = $this->getParameter($paramName, self::SCOPE_GLOBAL);
        $relativeScopeParameter = $this->getParameter($paramName, $this->siteAccess->name);
        $this->containerMock
             ->expects($this->exactly(2))
             ->method('hasParameter')
             ->with(
                 $this->logicalOr(
                     $globalScopeParameter,
                     $relativeScopeParameter
                 )
             )
             // First call is for "global" scope, second is the right one
             ->will($this->onConsecutiveCalls(false, true));
        $this->containerMock
             ->expects($this->once())
             ->method('getParameter')
             ->with($relativeScopeParameter)
             ->willReturn($expectedValue);

        $this->assertSame($expectedValue, $this->getChainConfigResolver()->getParameter($paramName));
    }

    /**
     * @dataProvider parameterProvider
     */
    public function testGetParameterSpecificScope(string $paramName, $expectedValue): void
    {
        $specificScopeParameter = $this->getParameter($paramName, self::FIRST_SA_NAME);
        $this->containerMock
             ->expects($this->exactly(2))
             ->method('hasParameter')
             ->with(
                 $this->logicalOr(
                     "ezsettings.global.$paramName",
                     $specificScopeParameter
                 )
             )
             // First call is for "global" scope, second is the right one
             ->will($this->onConsecutiveCalls(false, true));
        $this->containerMock
             ->expects($this->once())
             ->method('getParameter')
             ->with($specificScopeParameter)
             ->willReturn($expectedValue);

        $this->assertSame(
             $expectedValue,
             $this->getChainConfigResolver()->getParameter($paramName, self::DEFAULT_NAMESPACE, self::FIRST_SA_NAME)
         );
    }

    /**
     * @dataProvider parameterProvider
     */
    public function testGetParameterGlobalScope(string $paramName, $expectedValue): void
    {
        $globalScopeParameter = $this->getParameter($paramName, self::SCOPE_GLOBAL);
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

        $this->assertSame($expectedValue, $this->getChainConfigResolver()->getParameter($paramName));
    }

    /**
     * @dataProvider hasParameterProvider
     */
    public function testHasParameterNoNamespace(
         bool $defaultMatch,
         bool $groupMatch,
         bool $scopeMatch,
         bool $globalMatch,
         bool $expectedResult
     ): void {
        $paramName = 'foo.bar';
        $groupName = self::SA_GROUP;

        $chainConfigResolver = $this->getChainConfigResolver();

        $this->containerMock->expects($this->atLeastOnce())
             ->method('hasParameter')
             ->willReturnMap(
                 [
                     ["ezsettings.default.$paramName", $defaultMatch],
                     ["ezsettings.$groupName.$paramName", $groupMatch],
                     ["ezsettings.{$this->siteAccess->name}.$paramName", $scopeMatch],
                     ["ezsettings.global.$paramName", $globalMatch],
                 ]
             );

        $this->assertSame($expectedResult, $chainConfigResolver->hasParameter($paramName));
    }

    /**
     * @dataProvider hasParameterProvider
     */
    public function testHasParameterWithNamespaceAndScope(
         bool $defaultMatch,
         bool $groupMatch,
         bool $scopeMatch,
         bool $globalMatch,
         bool $expectedResult
     ): void {
        $paramName = 'foo.bar';
        $namespace = 'my.namespace';
        $scope = self::SECOND_SA_NAME;
        $groupName = self::SA_GROUP;

        $chainConfigResolver = $this->getChainConfigResolver();

        $this->containerMock->expects($this->atLeastOnce())
             ->method('hasParameter')
             ->willReturnMap(
                 [
                     ["$namespace.default.$paramName", $defaultMatch],
                     ["$namespace.$groupName.$paramName", $groupMatch],
                     ["$namespace.$scope.$paramName", $scopeMatch],
                     ["$namespace.global.$paramName", $globalMatch],
                 ]
             );

        $this->assertSame($expectedResult, $chainConfigResolver->hasParameter($paramName, $namespace, $scope));
    }

    private function getGlobalConfigResolver(string $defaultNamespace = self::DEFAULT_NAMESPACE): ConfigResolverInterface
    {
        $configResolver = new GlobalScopeConfigResolver(
             $defaultNamespace
         );
        $configResolver->setContainer($this->containerMock);

        return $configResolver;
    }

    private function getDefaultConfigResolver(string $defaultNamespace = self::DEFAULT_NAMESPACE): ConfigResolverInterface
    {
        $configResolver = new DefaultScopeConfigResolver(
             $defaultNamespace
         );
        $configResolver->setContainer($this->containerMock);

        return $configResolver;
    }

    protected function getSiteAccessGroupConfigResolver(string $defaultNamespace = self::DEFAULT_NAMESPACE): ConfigResolverInterface
    {
        $siteAccess = new SiteAccess(
             self::FIRST_SA_NAME,
         );
        $configResolver = new SiteAccessGroupConfigResolver(
             $this->getStaticSiteAccessProvider(),
             $defaultNamespace,
             []
         );
        $configResolver->setContainer($this->containerMock);
        $configResolver->setSiteAccess($siteAccess);

        return $configResolver;
    }

    protected function getSiteAccessConfigResolver(string $defaultNamespace = self::DEFAULT_NAMESPACE): ConfigResolverInterface
    {
        $siteAccess = new SiteAccess(
             self::FIRST_SA_NAME,
         );
        $configResolver = new StaticSiteAccessConfigResolver(
             $this->getStaticSiteAccessProvider(),
             $defaultNamespace
         );
        $configResolver->setContainer($this->containerMock);
        $configResolver->setSiteAccess($siteAccess);

        return $configResolver;
    }

    private function getStaticSiteAccessProvider(): StaticSiteAccessProvider
    {
        return new StaticSiteAccessProvider(
             [
                 self::FIRST_SA_NAME,
                 self::SECOND_SA_NAME,
             ],
             [
                 self::FIRST_SA_NAME => [self::SA_GROUP],
                 self::SECOND_SA_NAME => [self::SA_GROUP],
             ],
         );
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

    public function hasParameterProvider(): array
    {
        return [
             [true, true, true, true, true],
             [true, true, true, false, true],
             [true, true, false, false, true],
             [false, false, false, false, false],
             [false, false, true, false, true],
             [false, false, false, true, true],
             [false, false, true, true, true],
             [false, true, false, false, true],
         ];
    }

    private function getChainConfigResolver(): ChainConfigResolver
    {
        $chainConfigResolver = new ChainConfigResolver();
        $chainConfigResolver->addResolver($this->getDefaultConfigResolver(), 0);
        $chainConfigResolver->addResolver($this->getSiteAccessGroupConfigResolver(), 50);
        $chainConfigResolver->addResolver($this->getSiteAccessConfigResolver(), 100);
        $chainConfigResolver->addResolver($this->getGlobalConfigResolver(), 255);

        return $chainConfigResolver;
    }

    private function getParameter(
        string $paramName,
        string $scope,
        string $namespace = self::DEFAULT_NAMESPACE
     ): string {
        return sprintf('%s.%s.%s', $namespace, $scope, $paramName);
    }
}
