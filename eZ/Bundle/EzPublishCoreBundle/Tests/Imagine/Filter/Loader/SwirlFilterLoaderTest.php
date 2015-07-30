<?php

/**
 * File containing the SwirlFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\SwirlFilterLoader;
use PHPUnit_Framework_TestCase;

class SwirlFilterLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filter;

    /**
     * @var SwirlFilterLoader
     */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->filter = $this->getMock('\eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\FilterInterface');
        $this->loader = new SwirlFilterLoader($this->filter);
    }

    public function testLoadNoOption()
    {
        $image = $this->getMock('\Imagine\Image\ImageInterface');
        $this->filter
            ->expects($this->never())
            ->method('setOption');

        $this->filter
            ->expects($this->once())
            ->method('apply')
            ->with($image)
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image));
    }

    /**
     * @dataProvider loadWithOptionProvider
     */
    public function testLoadWithOption($degrees)
    {
        $image = $this->getMock('\Imagine\Image\ImageInterface');
        $this->filter
            ->expects($this->once())
            ->method('setOption')
            ->with('degrees', $degrees);

        $this->filter
            ->expects($this->once())
            ->method('apply')
            ->with($image)
            ->will($this->returnValue($image));

        $this->assertSame($image, $this->loader->load($image, array($degrees)));
    }

    public function loadWithOptionProvider()
    {
        return array(
            array(10),
            array(60),
            array(60.34),
            array(180.123),
        );
    }
}
