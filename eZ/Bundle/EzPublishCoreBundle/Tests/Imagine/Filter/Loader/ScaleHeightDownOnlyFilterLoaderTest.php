<?php

/**
 * File containing the ScaleHeightDownOnlyFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\ScaleHeightDownOnlyFilterLoader;
use PHPUnit\Framework\TestCase;

class ScaleHeightDownOnlyFilterLoaderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $innerLoader;

    /**
     * @var ScaleHeightDownOnlyFilterLoader
     */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->innerLoader = $this->createMock('Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface');
        $this->loader = new ScaleHeightDownOnlyFilterLoader();
        $this->loader->setInnerLoader($this->innerLoader);
    }

    /**
     * @expectedException \Imagine\Exception\InvalidArgumentException
     */
    public function testLoadInvalid()
    {
        $this->loader->load($this->createMock('\Imagine\Image\ImageInterface'), []);
    }

    public function testLoad()
    {
        $height = 123;
        $image = $this->createMock('\Imagine\Image\ImageInterface');
        $this->innerLoader
            ->expects($this->once())
            ->method('load')
            ->with($image, $this->equalTo(['size' => [null, $height], 'mode' => 'inset']))
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image, [$height]));
    }
}
