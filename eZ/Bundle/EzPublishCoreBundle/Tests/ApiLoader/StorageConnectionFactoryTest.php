<?php

/**
 * File containing the StorageConnectionFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageConnectionFactory;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use PHPUnit\Framework\TestCase;

class StorageConnectionFactoryTest extends TestCase
{
    /**
     * @dataProvider getConnectionProvider
     */
    public function testGetConnection($repositoryAlias, $doctrineConnection)
    {
        $repositories = array(
            $repositoryAlias => array(
                'storage' => array(
                    'engine' => 'legacy',
                    'connection' => $doctrineConnection,
                ),
            ),
        );

        $configResolver = $this->createMock('eZ\\Publish\\Core\\MVC\\ConfigResolverInterface');
        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('repository')
            ->will($this->returnValue($repositoryAlias));

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->once())
            ->method('has')
            ->with("doctrine.dbal.{$doctrineConnection}_connection")
            ->will($this->returnValue(true));
        $container
            ->expects($this->once())
            ->method('get')
            ->with("doctrine.dbal.{$doctrineConnection}_connection")
            ->will($this->returnValue($this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock()));

        $repositoryConfigurationProvider = new RepositoryConfigurationProvider($configResolver, $repositories);
        $factory = new StorageConnectionFactory($repositoryConfigurationProvider);
        $factory->setContainer($container);
        $connection = $factory->getConnection();
        $this->assertInstanceOf(
            'Doctrine\DBAL\Connection',
            $connection
        );
    }

    public function getConnectionProvider()
    {
        return array(
            array('my_repository', 'my_doctrine_connection'),
            array('foo', 'default'),
            array('répository_de_dédé', 'la_connexion_de_bébêrt'),
        );
    }

    /**
     * @expectedException \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidRepositoryException
     */
    public function testGetConnectionInvalidRepository()
    {
        $repositories = array(
            'foo' => array(
                'storage' => array(
                    'engine' => 'legacy',
                    'connection' => 'my_doctrine_connection',
                ),
            ),
        );

        $configResolver = $this->createMock('eZ\\Publish\\Core\\MVC\\ConfigResolverInterface');
        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('repository')
            ->will($this->returnValue('inexistent_repository'));

        $repositoryConfigurationProvider = new RepositoryConfigurationProvider($configResolver, $repositories);
        $factory = new StorageConnectionFactory($repositoryConfigurationProvider);
        $factory->setContainer($this->createMock('Symfony\Component\DependencyInjection\ContainerInterface'));
        $factory->getConnection();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetConnectionInvalidConnection()
    {
        $repositoryConfigurationProviderMock = $this->getMockBuilder('eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $repositoryConfig = array(
            'alias' => 'foo',
            'storage' => array(
                'engine' => 'legacy',
                'connection' => 'my_doctrine_connection',
            ),
        );
        $repositoryConfigurationProviderMock
            ->expects($this->once())
            ->method('getRepositoryConfig')
            ->will($this->returnValue($repositoryConfig));

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->once())
            ->method('has')
            ->with('doctrine.dbal.my_doctrine_connection_connection')
            ->will($this->returnValue(false));
        $container
            ->expects($this->once())
            ->method('getParameter')
            ->with('doctrine.connections')
            ->will($this->returnValue(array()));
        $factory = new StorageConnectionFactory($repositoryConfigurationProviderMock);
        $factory->setContainer($container);
        $factory->getConnection();
    }
}
