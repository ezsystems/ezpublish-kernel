<?php

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\OverlayBaseLoader;
use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\OverlayGradientVerticalLoader;
use Imagine\Image\Fill\Gradient\Vertical;
use Imagine\Image\ImageInterface;
use PHPUnit\Framework\TestCase;

class OverlayGradientVerticalLoaderTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $innerLoader;

    /**
     * @var OverlayGradientVerticalLoader
     */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->innerLoader = $this->createMock(OverlayBaseLoader::class);
        $this->loader = new OverlayGradientVerticalLoader();
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
            'empty_params' => [[]],
            'missing_start_end_colors' => [[123]],
            'fake_params' => [['foo' => 'bar']],
            'missing_end_color' => [[123, 456]],
        ];
    }

    public function testLoad()
    {
        $opacity = 30;
        $startColor = [0, 0, 0];
        $endColor = '+125';

        $image = $this->createMock(ImageInterface::class);
        $this->innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($image, [
                'opacity'    => $opacity,
                'startColor' => $startColor,
                'endColor'   => $endColor,
                'linerClass' => Vertical::class
            ])
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image, [$opacity, $startColor, $endColor]));
    }
}
