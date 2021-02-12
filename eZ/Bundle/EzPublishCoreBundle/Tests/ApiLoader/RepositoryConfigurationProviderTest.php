<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;

class RepositoryConfigurationProviderTest extends TestCase
{
    public function testGetRepositoryConfigSpecifiedRepository()
    {
        $configResolver = $this->getConfigResolverMock();
        $repositoryAlias = 'main';
        $repositoryConfig = [
            'engine' => 'foo',
            'connection' => 'some_connection',
        ];
        $repositories = [
            $repositoryAlias => $repositoryConfig,
            'another' => [
                'engine' => 'bar',
            ],
        ];
        $provider = new RepositoryConfigurationProvider($configResolver, $repositories);

        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('repository')
            ->will($this->returnValue($repositoryAlias));

        $this->assertSame(
            ['alias' => $repositoryAlias] + $repositoryConfig,
            $provider->getRepositoryConfig()
        );
    }

    public function testGetRepositoryConfigNotSpecifiedRepository()
    {
        $configResolver = $this->getConfigResolverMock();
        $repositoryAlias = 'main';
        $repositoryConfig = [
            'engine' => 'foo',
            'connection' => 'some_connection',
        ];
        $repositories = [
            $repositoryAlias => $repositoryConfig,
            'another' => [
                'engine' => 'bar',
            ],
        ];
        $provider = new RepositoryConfigurationProvider($configResolver, $repositories);

        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('repository')
            ->will($this->returnValue(null));

        $this->assertSame(
            ['alias' => $repositoryAlias] + $repositoryConfig,
            $provider->getRepositoryConfig()
        );
    }

    /**
     * @dataProvider providerForRepositories
     *
     * @expectedException \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidRepositoryException
     */
    public function testGetRepositoryConfigUndefinedRepository(array $repositories): void
    {
        $configResolver = $this->getConfigResolverMock();

        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('repository')
            ->will($this->returnValue('undefined_repository'));

        $provider = new RepositoryConfigurationProvider($configResolver, $repositories);
        $provider->getRepositoryConfig();
    }

    /**
     * @dataProvider providerForRepositories
     */
    public function testGetDefaultRepositoryAlias(array $repositories): void
    {
        $configResolver = $this->getConfigResolverMock();

        $provider = new RepositoryConfigurationProvider($configResolver, $repositories);
        $provider->getRepositoryConfig();

        self::assertSame('first', $provider->getDefaultRepositoryAlias());
    }

    /**
     * @dataProvider providerForRepositories
     */
    public function testGetCurrentRepositoryAlias(array $repositories): void
    {
        $configResolver = $this->getConfigResolverMock();

        $provider = new RepositoryConfigurationProvider($configResolver, $repositories);
        $provider->getRepositoryConfig();

        self::assertSame('first', $provider->getCurrentRepositoryAlias());
    }

    public function providerForRepositories(): array
    {
        return [
            [
                [
                    'first' => [
                        'engine' => 'foo',
                    ],
                    'second' => [
                        'engine' => 'bar',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected function getConfigResolverMock()
    {
        return $this->createMock(ConfigResolverInterface::class);
    }
}
