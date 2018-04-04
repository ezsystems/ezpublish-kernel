<?php

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\OverlayBaseLoader;
use Imagine\Image\BoxInterface;
use Imagine\Image\Fill\Gradient\Horizontal;
use Imagine\Image\Fill\Gradient\Vertical;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette\PaletteInterface;
use PHPUnit\Framework\TestCase;

class OverlayBaseLoaderTest extends TestCase
{
    /**
     * @var OverlayBaseLoader
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
            'missing_all_params' => [[]],
            'missing_start_end_colors_liner_class' => [['opacity' => 30]],
            'missing_end_color_liner_class' => [['opacity' => 30, 'startColor' => [0, 0, 0]]],
            'missing_liner_class' => [['opacity' => 30, 'startColor' => [0, 0, 0], 'endColor' => [0, 0, 0]]],
            'invalid)liner_class' => [['opacity' => 30, 'startColor' => [0, 0, 0], 'endColor' => [0, 0, 0], 'linerClass' => '']],
            'liner_horizontal_missing_end_color' => [['opacity' => 30, 'startColor' => [0, 0, 0], 'linerClass' => Horizontal::class]],
            'liner_vertical_missing_end_color' => [['opacity' => 30, 'startColor' => [0, 0, 0], 'linerClass' => Vertical::class]],
            'liner_vertical_missing_start_color' => [['opacity' => 30, 'endColor' => [0, 0, 0], 'linerClass' => Vertical::class]],
            'liner_horizontal_missing_start_color' => [['opacity' => 30, 'endColor' => [0, 0, 0], 'linerClass' => Horizontal::class]],
            'missing_opacity' => [['startColor' => [0, 0, 0], 'endColor' => [0, 0, 0], 'linerClass' => Vertical::class]],
            'invalid_opacity' => [['opacity' => [30], 'startColor' => [0, 0, 0], 'endColor' => [0, 0, 0], 'linerClass' => Vertical::class]],
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
