<?php

/**
 * File containing the LazyRepositoryFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\LazyRepositoryFactory;

class LazyRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildRepository()
    {
        $repositoryMock = $this->getMock('eZ\\Publish\\API\\Repository\\Repository');
        $factory = new LazyRepositoryFactory($repositoryMock);
        $lazyRepository = $factory->buildRepository();
        $this->assertTrue(is_callable($lazyRepository));

        // Calling several times to ensure container is called only once.
        $this->assertSame($repositoryMock, $lazyRepository());
        $this->assertSame($repositoryMock, $lazyRepository());
        $this->assertSame($repositoryMock, $lazyRepository());
    }
}
