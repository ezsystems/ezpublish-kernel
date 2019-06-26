<?php

/**
 * File containing the SwirlFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\FilterInterface;
use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\SwirlFilterLoader;
use Imagine\Image\ImageInterface;
use PHPUnit\Framework\TestCase;

class SwirlFilterLoaderTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $filter;

    /** @var SwirlFilterLoader */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->filter = $this->createMock(FilterInterface::class);
        $this->loader = new SwirlFilterLoader($this->filter);
    }

    public function testLoadNoOption()
    {
        $image = $this->createMock(ImageInterface::class);
        $this->filter
            ->expects($this->never())
            ->method('setOption');

        $this->filter
            ->expects($this->once())
            ->method('apply')
            ->with($image)
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image));
    }

    /**
     * @dataProvider loadWithOptionProvider
     */
    public function testLoadWithOption($degrees)
    {
        $image = $this->createMock(ImageInterface::class);
        $this->filter
            ->expects($this->once())
            ->method('setOption')
            ->with('degrees', $degrees);

        $this->filter
            ->expects($this->once())
            ->method('apply')
            ->with($image)
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image, [$degrees]));
    }

    public function loadWithOptionProvider()
    {
        return [
            [10],
            [60],
            [60.34],
            [180.123],
        ];
    }
}
