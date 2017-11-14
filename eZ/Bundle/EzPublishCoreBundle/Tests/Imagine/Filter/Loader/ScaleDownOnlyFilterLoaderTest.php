<?php

/**
 * File containing the ScaleDownOnlyFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\ScaleDownOnlyFilterLoader;
use Imagine\Image\ImageInterface;
use PHPUnit\Framework\TestCase;

class ScaleDownOnlyFilterLoaderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $innerLoader;

    /**
     * @var ScaleDownOnlyFilterLoader
     */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->innerLoader = $this->createMock('Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface');
        $this->loader = new ScaleDownOnlyFilterLoader();
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
        $options = array(123, 456);
        $image = $this->createMock('\Imagine\Image\ImageInterface');
        $this->innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($image, $this->equalTo(array('size' => $options, 'mode' => ImageInterface::THUMBNAIL_INSET)))
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image, $options));
    }
}
