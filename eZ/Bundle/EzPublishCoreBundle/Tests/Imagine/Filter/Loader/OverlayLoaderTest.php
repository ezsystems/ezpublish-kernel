<?php

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use Imagine\Image\Fill\Gradient\Horizontal;
use Imagine\Image\ImageInterface;
use ImagineCustomBundle\Loader\OverlayBaseLoader;
use ImagineCustomBundle\Loader\OverlayLoader;
use PHPUnit\Framework\TestCase;

class OverlayLoaderTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $innerLoader;

    /**
     * @var OverlayLoader
     */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->innerLoader = $this->createMock(OverlayBaseLoader::class);
        $this->loader      = new OverlayLoader();
        $this->loader->setInnerLoader($this->innerLoader);
    }

    /**
     * @expectedException \Imagine\Exception\InvalidArgumentException
     * @dataProvider loadInvalidProvider
     */
    public function testLoadInvalidOptions(array $options)
    {
        $this->loader->load($this->createMock(ImageInterface::class), $options);
    }

    public function loadInvalidProvider()
    {
        return [
            [[]],
            [[123]],
            [['foo' => 'bar']],
            [[123, null]],
        ];
    }

    public function testLoad()
    {
        $opacity = 30;
        $color   = [0, 0, 0];

        $image = $this->createMock(ImageInterface::class);
        $this->innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($image, [
                'opacity'    => $opacity,
                'startColor' => $color,
                'endColor'   => $color,
                'linerClass' => Horizontal::class
            ])
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image, [$opacity, $color, $color]));
    }
}
