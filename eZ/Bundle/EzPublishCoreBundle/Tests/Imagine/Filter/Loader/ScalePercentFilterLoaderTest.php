<?php

/**
 * File containing the ScalePercentFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\ScalePercentFilterLoader;
use Imagine\Image\Box;
use PHPUnit\Framework\TestCase;

class ScalePercentFilterLoaderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $innerLoader;

    /**
     * @var ScalePercentFilterLoader
     */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->innerLoader = $this->createMock('\Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface');
        $this->loader = new ScalePercentFilterLoader();
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
        );
    }

    public function testLoad()
    {
        $widthPercent = 40;
        $heightPercent = 125;
        $origWidth = 770;
        $origHeight = 377;
        $expectedWidth = ($origWidth * $widthPercent) / 100;
        $expectedHeight = ($origHeight * $heightPercent) / 100;

        $box = new Box($origWidth, $origHeight);
        $image = $this->createMock('\Imagine\Image\ImageInterface');
        $image
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue($box));

        $this->innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($image, $this->equalTo(array('size' => array($expectedWidth, $expectedHeight))))
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image, array($widthPercent, $heightPercent)));
    }
}
