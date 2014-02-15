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
    public function testBuildLegacyDbHandler()
    {
        $doctrineConnection = 'my_doctrine_connection';
        $repositoryAlias = 'my_repository';
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
}
