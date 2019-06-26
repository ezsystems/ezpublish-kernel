<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Cache\Resolver;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Cache\Resolver\RelativeResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use PHPUnit\Framework\TestCase;

class RelativeResolverTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface */
    private $liipResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->liipResolver = $this->getMockBuilder(ResolverInterface::class)->getMock();
    }

    public function testResolve()
    {
        $resolver = new RelativeResolver($this->liipResolver);

        $path = '7/4/2/0/247-1-eng-GB/test.png';
        $filter = 'big';

        $absolute = 'http://ez.no/var/site/storage/images/_aliases/big/7/4/2/0/247-1-eng-GB/test.png';
        $expected = '/var/site/storage/images/_aliases/big/7/4/2/0/247-1-eng-GB/test.png';

        $this->liipResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($path, $filter)
            ->willReturn($absolute);

        $this->assertSame($expected, $resolver->resolve($path, $filter));
    }
}
