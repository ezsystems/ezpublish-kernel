<?php

/**
 * File containing the ScaleWidthDownOnlyFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\ScaleWidthDownOnlyFilterLoader;
use Imagine\Image\ImageInterface;
use PHPUnit\Framework\TestCase;

class ScaleWidthDownOnlyFilterLoaderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $innerLoader;

    /**
     * @var ScaleWidthDownOnlyFilterLoader
     */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->innerLoader = $this->createMock('Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface');
        $this->loader = new ScaleWidthDownOnlyFilterLoader();
        $this->loader->setInnerLoader($this->innerLoader);
    }

    /**
     * @expectedException \Imagine\Exception\InvalidArgumentException
     */
    public function testLoadInvalid()
    {
        $this->loader->load($this->createMock('\Imagine\Image\ImageInterface'), array());
    }

    public function testLoad()
    {
        $width = 123;
        $image = $this->createMock('\Imagine\Image\ImageInterface');
        $this->innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($image, $this->equalTo(array('size' => array($width, null), 'mode' => ImageInterface::THUMBNAIL_INSET)))
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image, array($width)));
    }
}
