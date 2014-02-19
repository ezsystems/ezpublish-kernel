<?php
/**
 * File containing the LegacyDbHandlerFactoryTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\LegacyDbHandlerFactory;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageEngineFactory;

class LegacyDbHandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider buildLegacyDbHandlerProvider
     */
    public function testBuildLegacyDbHandler( $repositoryAlias, $doctrineConnection )
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
            ->method( 'hasParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( true ) );
        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( $repositoryAlias ) );

        $container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
        $container
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'ezpublish.api.storage_engine.legacy.dbhandler.class' )
            ->will( $this->returnValue( 'eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler' ) );
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

        $storageEngineFactory = new StorageEngineFactory( $configResolver, $repositories );
        $factory = new LegacyDbHandlerFactory( $storageEngineFactory );
        $factory->setContainer( $container );
        $handler = $factory->buildLegacyDbHandler();
        $this->assertInstanceOf(
            'eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler',
            $handler
        );
    }

    public function buildLegacyDbHandlerProvider()
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
    public function testBuildLegacyDbHandlerInvalidRepository()
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
            ->method( 'hasParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( true ) );
        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( 'inexistent_repository' ) );

        $storageEngineFactory = new StorageEngineFactory( $configResolver, $repositories );
        $factory = new LegacyDbHandlerFactory( $storageEngineFactory );
        $factory->setContainer( $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' ) );
        $factory->buildLegacyDbHandler();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildLegacyDbHandlerInvalidConnection()
    {
        $storageEngineFactoryMock = $this->getMockBuilder( 'eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageEngineFactory' )
            ->disableOriginalConstructor()
            ->getMock();
        $repositoryConfig = array(
            'alias' => 'foo',
            'engine' => 'legacy',
            'connection' => 'my_doctrine_connection'
        );
        $storageEngineFactoryMock
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
        $factory = new LegacyDbHandlerFactory( $storageEngineFactoryMock );
        $factory->setContainer( $container );
        $factory->buildLegacyDbHandler();
    }
}
