<?php

/**
 * File containing the LazyRepositoryFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\LazyRepositoryFactory;
use PHPUnit\Framework\TestCase;

class LazyRepositoryFactoryTest extends TestCase
{
    public function testBuildRepository()
    {
        $repositoryMock = $this->createMock('eZ\\Publish\\API\\Repository\\Repository');
        $factory = new LazyRepositoryFactory($repositoryMock);
        $lazyRepository = $factory->buildRepository();
        $this->assertInternalType('callable', $lazyRepository);

        // Calling several times to ensure container is called only once.
        $this->assertSame($repositoryMock, $lazyRepository());
        $this->assertSame($repositoryMock, $lazyRepository());
        $this->assertSame($repositoryMock, $lazyRepository());
    }
}
