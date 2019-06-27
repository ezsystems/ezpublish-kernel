<?php

/**
 * File containing the ScaleWidthFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\ScaleWidthFilterLoader;
use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use PHPUnit\Framework\TestCase;

class ScaleWidthFilterLoaderTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $innerLoader;

    /** @var ScaleWidthFilterLoader */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->innerLoader = $this->createMock(LoaderInterface::class);
        $this->loader = new ScaleWidthFilterLoader();
        $this->loader->setInnerLoader($this->innerLoader);
    }

    /**
     * @expectedException \Imagine\Exception\InvalidArgumentException
     */
    public function testLoadFail()
    {
        $this->loader->load($this->createMock(ImageInterface::class, []));
    }

    public function testLoad()
    {
        $width = 123;
        $image = $this->createMock(ImageInterface::class);
        $this->innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($image, $this->equalTo(['widen' => $width]))
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image, [$width]));
    }
}
