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
            ->expects( $this->any() )
            ->method( 'get' )
            ->with( "doctrine.dbal.{$doctrineConnection}_connection" )
            ->will( $this->returnValue( $this->getMock( 'Doctrine\DBAL\Driver\Connection' ) ) );

        $factory = new LegacyDbHandlerFactory( $configResolver, $repositories );
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
            ->method( 'getParameter' )
            ->with( 'repository' )
            ->will( $this->returnValue( 'inexistent_repository' ) );

        $factory = new LegacyDbHandlerFactory( $configResolver, $repositories );
        $factory->setContainer( $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' ) );
        $handler = $factory->buildLegacyDbHandler();
    }
}
