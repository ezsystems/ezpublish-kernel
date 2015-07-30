<?php

/**
 * File containing the BorderFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\BorderFilterLoader;
use PHPUnit_Framework_TestCase;

class BorderFilterLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Imagine\Exception\InvalidArgumentException
     * @dataProvider loadInvalidProvider
     */
    public function testLoadInvalidOptions(array $options)
    {
        $loader = new BorderFilterLoader();
        $loader->load($this->getMock('\Imagine\Image\ImageInterface'), $options);
    }

    public function loadInvalidProvider()
    {
        return array(
            array(array()),
            array(array(123)),
            array(array('foo' => 'bar')),
        );
    }

    public function testLoadDefaultColor()
    {
        $image = $this->getMock('\Imagine\Image\ImageInterface');
        $options = array(10, 10);

        $palette = $this->getMock('\Imagine\Image\Palette\PaletteInterface');
        $image
            ->expects($this->once())
            ->method('palette')
            ->will($this->returnValue($palette));
        $palette
            ->expects($this->once())
            ->method('color')
            ->with(BorderFilterLoader::DEFAULT_BORDER_COLOR)
            ->will($this->returnValue($this->getMock('\Imagine\Image\Palette\Color\ColorInterface')));

        $box = $this->getMock('\Imagine\Image\BoxInterface');
        $image
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue($box));
        $box
            ->expects($this->any())
            ->method('getWidth')
            ->will($this->returnValue(100));
        $box
            ->expects($this->any())
            ->method('getHeight')
            ->will($this->returnValue(100));

        $drawer = $this->getMock('\Imagine\Draw\DrawerInterface');
        $image
            ->expects($this->once())
            ->method('draw')
            ->will($this->returnValue($drawer));
        $drawer
            ->expects($this->any())
            ->method('line')
            ->will($this->returnValue($drawer));

        $loader = new BorderFilterLoader();
        $this->assertSame($image, $loader->load($image, $options));
    }

    /**
     * @dataProvider loadProvider
     */
    public function testLoad($thickX, $thickY, $color)
    {
        $image = $this->getMock('\Imagine\Image\ImageInterface');
        $options = array($thickX, $thickY, $color);

        $palette = $this->getMock('\Imagine\Image\Palette\PaletteInterface');
        $image
            ->expects($this->once())
            ->method('palette')
            ->will($this->returnValue($palette));
        $palette
            ->expects($this->once())
            ->method('color')
            ->with($color)
            ->will($this->returnValue($this->getMock('\Imagine\Image\Palette\Color\ColorInterface')));

        $box = $this->getMock('\Imagine\Image\BoxInterface');
        $image
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue($box));
        $box
            ->expects($this->any())
            ->method('getWidth')
            ->will($this->returnValue(1000));
        $box
            ->expects($this->any())
            ->method('getHeight')
            ->will($this->returnValue(1000));

        $drawer = $this->getMock('\Imagine\Draw\DrawerInterface');
        $image
            ->expects($this->once())
            ->method('draw')
            ->will($this->returnValue($drawer));
        $drawer
            ->expects($this->any())
            ->method('line')
            ->will($this->returnValue($drawer));

        $loader = new BorderFilterLoader();
        $this->assertSame($image, $loader->load($image, $options));
    }

    public function loadProvider()
    {
        return array(
            array(10, 10, '#fff'),
            array(5, 5, '#5dcb4f'),
            array(50, 50, '#fa1629'),
        );
    }
}
