<?php
/**
 * File containing the StorageRepositoryProviderTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider;
use PHPUnit_Framework_TestCase;

class StorageRepositoryProviderTest extends PHPUnit_Framework_TestCase
{
    public function testGetRepositoryConfigSpecifiedRepository()
    {
        $configResolver = $this->getConfigResolverMock();
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
        $provider = new StorageRepositoryProvider( $configResolver, $repositories );

        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( $repositoryAlias ) );

        $this->assertSame(
            array( 'alias' => $repositoryAlias ) + $repositoryConfig,
            $provider->getRepositoryConfig()
        );
    }

    public function testGetRepositoryConfigNotSpecifiedRepository()
    {
        $configResolver = $this->getConfigResolverMock();
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
        $provider = new StorageRepositoryProvider( $configResolver, $repositories );

        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( null ) );

        $this->assertSame(
            array( 'alias' => $repositoryAlias ) + $repositoryConfig,
            $provider->getRepositoryConfig()
        );
    }

    /**
     * @expectedException \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidRepositoryException
     */
    public function testGetRepositoryConfigUndefinedRepository()
    {
        $configResolver = $this->getConfigResolverMock();
        $repositories = array(
            'main' => array(
                'engine' => 'foo'
            ),
            'another' => array(
                'engine' => 'bar'
            )
        );

        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( 'undefined_repository' ) );

        $provider = new StorageRepositoryProvider( $configResolver, $repositories );
        $provider->getRepositoryConfig();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected function getConfigResolverMock()
    {
        return $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
    }
}
