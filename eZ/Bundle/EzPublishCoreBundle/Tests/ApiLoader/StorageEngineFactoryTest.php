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
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider;
use PHPUnit_Framework_TestCase;

class StorageEngineFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterStorageEngine()
    {
        $factory = new StorageEngineFactory(
            $this
                ->getMockBuilder( 'eZ\\Bundle\\EzPublishCoreBundle\\ApiLoader\\StorageRepositoryProvider' )
                ->disableOriginalConstructor()
                ->getMock()
        );

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
        $storageRepositoryProvider = new StorageRepositoryProvider( $configResolver, $repositories );
        $factory = new StorageEngineFactory( $storageRepositoryProvider );
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

        $storageRepositoryProvider = new StorageRepositoryProvider( $configResolver, $repositories );
        $factory = new StorageEngineFactory( $storageRepositoryProvider );
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
}
