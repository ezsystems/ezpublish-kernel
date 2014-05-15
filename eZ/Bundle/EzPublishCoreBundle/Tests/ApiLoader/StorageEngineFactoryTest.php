<?php
/**
 * File containing the StorageEngineFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
        /** @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider $storageRepositoryProvider */
        $storageRepositoryProvider = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishCoreBundle\\ApiLoader\\StorageRepositoryProvider' )
            ->disableOriginalConstructor()
            ->getMock();
        $factory = new StorageEngineFactory( $storageRepositoryProvider );

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
        $configResolver = $this->getConfigResolverMock();
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
        $configResolver = $this->getConfigResolverMock();
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected function getConfigResolverMock()
    {
        return $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
    }
}
