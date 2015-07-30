<?php

/**
 * File containing the ScaleFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\ScaleFilterLoader;
use Imagine\Image\Box;
use PHPUnit_Framework_TestCase;

class ScaleFilterLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $innerLoader;

    /**
     * @var ScaleFilterLoader
     */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->innerLoader = $this->getMock('\Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface');
        $this->loader = new ScaleFilterLoader();
        $this->loader->setInnerLoader($this->innerLoader);
    }

    /**
     * @expectedException \Imagine\Exception\InvalidArgumentException
     * @dataProvider loadInvalidProvider
     */
    public function testLoadInvalidOptions(array $options)
    {
        $this->loader->load($this->getMock('\Imagine\Image\ImageInterface'), $options);
    }

    public function loadInvalidProvider()
    {
        return array(
            array(array()),
            array(array(123)),
            array(array('foo' => 'bar')),
        );
    }

    public function testLoadHeighten()
    {
        $width = 900;
        $height = 400;
        $origWidth = 770;
        $origHeight = 377;
        $box = new Box($origWidth, $origHeight);

        $image = $this->getMock('\Imagine\Image\ImageInterface');
        $image
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue($box));

        $this->innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($image, $this->equalTo(array('heighten' => $height)))
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image, array($width, $height)));
    }

    public function testLoadWiden()
    {
        $width = 900;
        $height = 600;
        $origWidth = 770;
        $origHeight = 377;
        $box = new Box($origWidth, $origHeight);

        $image = $this->getMock('\Imagine\Image\ImageInterface');
        $image
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue($box));

        $this->innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($image, $this->equalTo(array('widen' => $width)))
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image, array($width, $height)));
    }
}
