<?php
/**
 * File containing the StorageEngineFactoryTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageEngineFactory;
use PHPUnit_Framework_TestCase;

class StorageEngineFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterStorageEngine()
    {
        $factory = new StorageEngineFactory( $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' ), array() );

        $storageEngines = array(
            'foo' => $this->getMock( 'eZ\Publish\SPI\Persistence\Handler' ),
            'bar' => $this->getMock( 'eZ\Publish\SPI\Persistence\Handler' ),
            'baz' => $this->getMock( 'eZ\Publish\SPI\Persistence\Handler' )
        );

        foreach ( $storageEngines as $identifier => $persistenceHandler )
        {
            $factory->registerStorageEngine( $persistenceHandler, $identifier );
        }

        $this->assertSame( $storageEngines, $factory->getStorageEngines() );
    }

    public function testBuildStorageEngine()
    {
        $configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $repositoryAlias = 'main';
        $repositories = array(
            $repositoryAlias => array(
                'engine' => 'foo'
            ),
            'another' => array(
                'engine' => 'bar'
            )
        );
        $expectedStorageEngine = $this->getMock( 'eZ\Publish\SPI\Persistence\Handler' );
        $storageEngines = array(
            'foo' => $expectedStorageEngine,
            'bar' => $this->getMock( 'eZ\Publish\SPI\Persistence\Handler' ),
            'baz' => $this->getMock( 'eZ\Publish\SPI\Persistence\Handler' )
        );
        $factory = new StorageEngineFactory( $configResolver, $repositories );
        foreach ( $storageEngines as $identifier => $persistenceHandler )
        {
            $factory->registerStorageEngine( $persistenceHandler, $identifier );
        }

        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( $repositoryAlias ) );

        $this->assertSame( $expectedStorageEngine, $factory->buildStorageEngine() );
    }

    /**
     * @expectedException \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidStorageEngine
     */
    public function testBuildInvalidStorageEngine()
    {
        $configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $repositoryAlias = 'main';
        $repositories = array(
            $repositoryAlias => array(
                'engine' => 'undefined_storage_engine'
            ),
            'another' => array(
                'engine' => 'bar'
            )
        );

        $storageEngines = array(
            'foo' => $this->getMock( 'eZ\Publish\SPI\Persistence\Handler' ),
            'bar' => $this->getMock( 'eZ\Publish\SPI\Persistence\Handler' ),
            'baz' => $this->getMock( 'eZ\Publish\SPI\Persistence\Handler' )
        );

        $factory = new StorageEngineFactory( $configResolver, $repositories );
        foreach ( $storageEngines as $identifier => $persistenceHandler )
        {
            $factory->registerStorageEngine( $persistenceHandler, $identifier );
        }

        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( $repositoryAlias ) );

        $this->assertSame( $this->getMock( 'eZ\Publish\SPI\Persistence\Handler' ), $factory->buildStorageEngine() );
    }

    public function testGetRepositoryConfigSpecifiedRepository()
    {
        $configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $repositoryAlias = 'main';
        $repositoryConfig = array(
            'engine' => 'foo',
            'connection' => 'some_connection'
        );
        $repositories = array(
            $repositoryAlias => $repositoryConfig,
            'another' => array(
                'engine' => 'bar'
            )
        );
        $factory = new StorageEngineFactory( $configResolver, $repositories );

        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( $repositoryAlias ) );

        $this->assertSame( array( 'alias' => $repositoryAlias ) + $repositoryConfig, $factory->getRepositoryConfig() );
    }

    public function testGetRepositoryConfigNotSpecifiedRepository()
    {
        $configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $repositoryAlias = 'main';
        $repositoryConfig = array(
            'engine' => 'foo',
            'connection' => 'some_connection'
        );
        $repositories = array(
            $repositoryAlias => $repositoryConfig,
            'another' => array(
                'engine' => 'bar'
            )
        );
        $factory = new StorageEngineFactory( $configResolver, $repositories );

        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( null ) );

        $this->assertSame( array( 'alias' => $repositoryAlias ) + $repositoryConfig, $factory->getRepositoryConfig() );
    }

    /**
     * @expectedException \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidRepositoryException
     */
    public function testGetRepositoryConfigUndefinedRepository()
    {
        $repositories = array(
            'main' => array(
                'engine' => 'foo'
            ),
            'another' => array(
                'engine' => 'bar'
            )
        );
        $configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );

        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( 'undefined_repository' ) );

        $factory = new StorageEngineFactory( $configResolver, $repositories );
        $factory->getRepositoryConfig();
    }
}
