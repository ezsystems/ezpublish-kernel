<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\PlaceholderProvider;

use eZ\Bundle\EzPublishCoreBundle\Imagine\PlaceholderProvider\GenericProvider;
use eZ\Publish\Core\FieldType\Image\Value as ImageValue;
use Imagine\Draw\DrawerInterface;
use Imagine\Image\AbstractFont;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use PHPUnit\Framework\TestCase;

class GenericProviderTest extends TestCase
{
    /**
     * @dataProvider getPlaceholderDataProvider
     */
    public function testGetPlaceholder(ImageValue $value, array $options = [], $expectedText)
    {
        $font = $this->createMock(AbstractFont::class);

        $imagine = $this->createMock(ImagineInterface::class);
        $imagine
            ->expects($this->atLeastOnce())
            ->method('font')
            ->willReturnCallback(function ($fontpath, $fontsize, ColorInterface $foreground) use ($options, $font) {
                $this->assertEquals($options['fontpath'], $fontpath);
                $this->assertEquals($options['fontsize'], $fontsize);
                $this->assertColorEquals($options['foreground'], $foreground);

                return $font;
            });

        $font
            ->expects($this->any())
            ->method('box')
            ->willReturn($this->createMock(BoxInterface::class));

        $image = $this->createMock(ImageInterface::class);

        $imagine
            ->expects($this->atLeastOnce())
            ->method('create')
            ->willReturnCallback(function (BoxInterface $size, ColorInterface $background) use ($value, $options, $image) {
                $this->assertSizeEquals([$value->width, $value->height], $size);
                $this->assertColorEquals($options['background'], $background);

                return $image;
            });

        $drawer = $this->createMock(DrawerInterface::class);
        $image
            ->expects($this->any())
            ->method('draw')
            ->willReturn($drawer);

        $drawer
            ->expects($this->atLeastOnce())
            ->method('text')
            ->with($expectedText, $font);

        $provider = new GenericProvider($imagine);
        $provider->getPlaceholder($value, $options);
    }

    public function getPlaceholderDataProvider()
    {
        return [
            [
                new ImageValue([
                    'id' => 'photo.jpg',
                    'width' => 640,
                    'height' => 480,
                ]),
                [
                    'background' => '#00FF00',
                    'foreground' => '#FF0000',
                    'fontsize' => 72,
                    'text' => "IMAGE PLACEHOLDER %width%x%height%\n(%id%)",
                    'fontpath' => '/path/to/font.ttf',
                ],
                "IMAGE PLACEHOLDER 640x480\n(photo.jpg)",
            ],
        ];
    }

    private function assertSizeEquals(array $expected, BoxInterface $actual)
    {
        $this->assertEquals($expected[0], $actual->getWidth());
        $this->assertEquals($expected[1], $actual->getHeight());
    }

    private function assertColorEquals($expected, ColorInterface $actual)
    {
        $this->assertEquals(strtolower($expected), strtolower((string)$actual));
    }
}
