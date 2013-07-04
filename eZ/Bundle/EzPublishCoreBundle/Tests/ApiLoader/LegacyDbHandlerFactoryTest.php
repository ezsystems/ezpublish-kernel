<?php
/**
 * File containing the LegacyDbHandlerFactoryTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\LegacyDbHandlerFactory;

class LegacyDbHandlerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildLegacyDbHandler()
    {
        $configResolver = $this->getMock( 'eZ\\Publish\\Core\\MVC\\ConfigResolverInterface' );
        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'database.params' )
            ->will( $this->returnValue( 'sqlite://:memory:' ) );

        $factory = new LegacyDbHandlerFactory( $configResolver );
        $handler = $factory->buildLegacyDbHandler();
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\EzcDbHandler',
            $handler
        );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\EzcDbHandler\\Sqlite',
            $handler
        );
    }
}
