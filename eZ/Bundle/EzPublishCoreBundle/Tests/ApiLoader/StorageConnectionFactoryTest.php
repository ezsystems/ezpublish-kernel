<?php
/**
 * File containing the StorageConnectionFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageConnectionFactory;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider;

class StorageConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getConnectionProvider
     */
    public function testGetConnection( $repositoryAlias, $doctrineConnection )
    {
        $repositories = array(
            $repositoryAlias => array(
                'engine' => 'legacy',
                'connection' => $doctrineConnection
            )
        );

        $configResolver = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( $repositoryAlias ) );

        $container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
        $container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( "doctrine.dbal.{$doctrineConnection}_connection" )
            ->will( $this->returnValue( true ) );
        $container
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( "doctrine.dbal.{$doctrineConnection}_connection" )
            ->will( $this->returnValue( $this->getMock( 'Doctrine\DBAL\Driver\Connection' ) ) );

        $storageRepositoryProvider = new StorageRepositoryProvider( $configResolver, $repositories );
        $factory = new StorageConnectionFactory( $storageRepositoryProvider );
        $factory->setContainer( $container );
        $connection = $factory->getConnection();
        $this->assertInstanceOf(
            'Doctrine\DBAL\Driver\Connection',
            $connection
        );
    }

    public function getConnectionProvider()
    {
        return array(
            array( 'my_repository', 'my_doctrine_connection' ),
            array( 'foo', 'default' ),
            array( 'répository_de_dédé', 'la_connexion_de_bébêrt' ),
        );
    }

    /**
     * @expectedException \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidRepositoryException
     */
    public function testGetConnectionInvalidRepository()
    {
        $repositories = array(
            'foo' => array(
                'engine' => 'legacy',
                'connection' => 'my_doctrine_connection'
            )
        );

        $configResolver = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( 'inexistent_repository' ) );

        $storageRepositoryProvider = new StorageRepositoryProvider( $configResolver, $repositories );
        $factory = new StorageConnectionFactory( $storageRepositoryProvider );
        $factory->setContainer( $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' ) );
        $factory->getConnection();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetConnectionInvalidConnection()
    {
        $storageRepositoryProviderMock = $this->getMockBuilder( 'eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider' )
            ->disableOriginalConstructor()
            ->getMock();
        $repositoryConfig = array(
            'alias' => 'foo',
            'engine' => 'legacy',
            'connection' => 'my_doctrine_connection'
        );
        $storageRepositoryProviderMock
            ->expects( $this->once() )
            ->method( 'getRepositoryConfig' )
            ->will( $this->returnValue( $repositoryConfig ) );

        $container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
        $container
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( "doctrine.dbal.my_doctrine_connection_connection" )
            ->will( $this->returnValue( false ) );
        $container
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'doctrine.connections' )
            ->will( $this->returnValue( array() ) );
        $factory = new StorageConnectionFactory( $storageRepositoryProviderMock );
        $factory->setContainer( $container );
        $factory->getConnection();
    }
}
