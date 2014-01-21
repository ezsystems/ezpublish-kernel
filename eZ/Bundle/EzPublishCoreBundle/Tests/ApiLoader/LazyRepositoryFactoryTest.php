<?php
/**
 * File containing the LazyRepositoryFactoryTest class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\LazyRepositoryFactory;

class LazyRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildRepository()
    {
        $repositoryMock = $this->getMock( 'eZ\\Publish\\API\\Repository\\Repository' );
        $factory = new LazyRepositoryFactory( $repositoryMock );
        $lazyRepository = $factory->buildRepository();
        $this->assertTrue( is_callable( $lazyRepository ) );

        // Calling several times to ensure container is called only once.
        $this->assertSame( $repositoryMock, $lazyRepository() );
        $this->assertSame( $repositoryMock, $lazyRepository() );
        $this->assertSame( $repositoryMock, $lazyRepository() );
    }
}
