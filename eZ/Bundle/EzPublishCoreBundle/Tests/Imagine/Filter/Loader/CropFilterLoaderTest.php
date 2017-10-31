<?php

/**
 * File containing the CropFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\CropFilterLoader;
use PHPUnit\Framework\TestCase;

class CropFilterLoaderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $innerLoader;

    /**
     * @var CropFilterLoader
     */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->innerLoader = $this->createMock('\Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface');
        $this->loader = new CropFilterLoader();
        $this->loader->setInnerLoader($this->innerLoader);
    }

    /**
     * @expectedException \Imagine\Exception\InvalidArgumentException
     * @dataProvider loadInvalidProvider
     */
    public function testLoadInvalidOptions(array $options)
    {
        $this->loader->load($this->createMock('\Imagine\Image\ImageInterface'), $options);
    }

    public function loadInvalidProvider()
    {
        return array(
            array(array()),
            array(array(123)),
            array(array('foo' => 'bar')),
            array(array(123, 456)),
            array(array(123, 456, 789)),
        );
    }

    public function testLoad()
    {
        $width = 123;
        $height = 789;
        $offsetX = 100;
        $offsetY = 200;

        $image = $this->createMock('\Imagine\Image\ImageInterface');
        $this->innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($image, array('size' => array($width, $height), 'start' => array($offsetX, $offsetY)))
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image, array($width, $height, $offsetX, $offsetY)));
    }
}
