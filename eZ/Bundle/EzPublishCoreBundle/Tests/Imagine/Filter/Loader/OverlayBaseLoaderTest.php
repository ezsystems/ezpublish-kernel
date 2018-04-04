<?php

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\CropFilterLoader;
use Imagine\Image\BoxInterface;
use Imagine\Image\Fill\Gradient\Horizontal;
use Imagine\Image\Fill\Gradient\Vertical;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette\PaletteInterface;
use ImagineCustomBundle\Loader\OverlayBaseLoader;
use PHPUnit\Framework\TestCase;

class OverlayBaseLoaderTest extends TestCase
{
    /**
     * @var CropFilterLoader
     */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->loader = new OverlayBaseLoader();
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
            [['opacity' => 30]],
            [['opacity' => 30, 'startColor' => [0, 0, 0]]],
            [['opacity' => 30, 'startColor' => [0, 0, 0], 'endColor' => [0, 0, 0]]],
            [['opacity' => 30, 'startColor' => [0, 0, 0], 'endColor' => [0, 0, 0], 'linerClass' => '']],
            [['opacity' => 30, 'startColor' => [0, 0, 0], 'linerClass' => Horizontal::class]],
            [['opacity' => 30, 'startColor' => [0, 0, 0], 'linerClass' => Vertical::class]],
            [['opacity' => 30, 'startColor' => [0, 0, 0], 'linerClass' => Vertical::class]],
            [['opacity' => 30, 'endColor' => [0, 0, 0], 'linerClass' => Vertical::class]],
            [['opacity' => 30, 'endColor' => [0, 0, 0], 'linerClass' => Vertical::class]],
            [['startColor' => [0, 0, 0], 'endColor' => [0, 0, 0], 'linerClass' => Vertical::class]],
            [['startColor' => [0, 0, 0], 'endColor' => [0, 0, 0], 'linerClass' => Vertical::class]],
            [['opacity' => [30], 'startColor' => [0, 0, 0], 'endColor' => [0, 0, 0], 'linerClass' => Vertical::class]],
            [['foo' => 'bar']],
            [[123, null]],
        ];
    }

    public function testOverlayLoad()
    {
        $options = [
            'opacity'    => 30,
            'startColor' => [0, 0, 0],
            'endColor'   => [0, 0, 0],
            'linerClass' => Horizontal::class
        ];

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('copy')
            ->will($this->returnValue($image));
        $image
            ->expects($this->once())
            ->method('fill')
            ->will($this->returnValue($image));
        $image
            ->expects($this->once())
            ->method('paste')
            ->will($this->returnValue($image));

        $palette = $this->createMock(PaletteInterface::class);
        $palette
            ->expects($this->exactly(2))
            ->method('color')
            ->with($options['startColor'], $options['opacity'])
            ->will($this->returnValue($this->createMock(ColorInterface::class)));

        $image
            ->expects($this->once())
            ->method('palette')
            ->will($this->returnValue($palette));

        $box = $this->createMock(BoxInterface::class);
        $box
            ->expects($this->any())
            ->method('getWidth')
            ->will($this->returnValue(100));
        $image
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue($box));


        $this->assertSame($image, $this->loader->load($image, $options));
    }


    public function testOverlayGradientHorizontalLoad()
    {
        $options = [
            'opacity'    => 30,
            'startColor' => [0, 0, 0],
            'endColor'   => [0, 0, 0],
            'linerClass' => Horizontal::class
        ];

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('copy')
            ->will($this->returnValue($image));
        $image
            ->expects($this->once())
            ->method('fill')
            ->will($this->returnValue($image));
        $image
            ->expects($this->once())
            ->method('paste')
            ->will($this->returnValue($image));

        $palette = $this->createMock(PaletteInterface::class);
        $palette
            ->expects($this->exactly(2))
            ->method('color')
            ->with($options['startColor'], $options['opacity'])
            ->will($this->returnValue($this->createMock(ColorInterface::class)));
        $image
            ->expects($this->once())
            ->method('palette')
            ->will($this->returnValue($palette));

        $box = $this->createMock(BoxInterface::class);
        $box
            ->expects($this->any())
            ->method('getWidth')
            ->will($this->returnValue(100));
        $image
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue($box));

        $this->assertSame($image, $this->loader->load($image, $options));
    }

    public function testOverlayGradientVerticalLoad()
    {
        $options = [
            'opacity'    => 30,
            'startColor' => [0, 0, 0],
            'endColor'   => [0, 0, 0],
            'linerClass' => Vertical::class
        ];

        $image = $this->createMock(ImageInterface::class);
        $image
            ->expects($this->once())
            ->method('copy')
            ->will($this->returnValue($image));
        $image
            ->expects($this->once())
            ->method('fill')
            ->will($this->returnValue($image));
        $image
            ->expects($this->once())
            ->method('paste')
            ->will($this->returnValue($image));

        $palette = $this->createMock(PaletteInterface::class);
        $palette
            ->expects($this->exactly(2))
            ->method('color')
            ->with($options['startColor'], $options['opacity'])
            ->will($this->returnValue($this->createMock(ColorInterface::class)));
        $image
            ->expects($this->once())
            ->method('palette')
            ->will($this->returnValue($palette));


        $box = $this->createMock(BoxInterface::class);
        $box
            ->expects($this->any())
            ->method('getHeight')
            ->will($this->returnValue(100));
        $image
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue($box));

        $this->assertSame($image, $this->loader->load($image, $options));
    }
}
