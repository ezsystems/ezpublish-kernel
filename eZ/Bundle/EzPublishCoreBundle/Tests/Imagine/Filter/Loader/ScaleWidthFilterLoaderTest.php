<?php

/**
 * File containing the ScaleWidthFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\ScaleWidthFilterLoader;
use PHPUnit_Framework_TestCase;

class ScaleWidthFilterLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $innerLoader;

    /**
     * @var ScaleWidthFilterLoader
     */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->innerLoader = $this->getMock('\Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface');
        $this->loader = new ScaleWidthFilterLoader();
        $this->loader->setInnerLoader($this->innerLoader);
    }

    /**
     * @expectedException \Imagine\Exception\InvalidArgumentException
     */
    public function testLoadFail()
    {
        $this->loader->load($this->getMock('\Imagine\Image\ImageInterface', array()));
    }

    public function testLoad()
    {
        $width = 123;
        $image = $this->getMock('\Imagine\Image\ImageInterface');
        $this->innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($image, $this->equalTo(array('widen' => $width)))
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image, array($width)));
    }
}
