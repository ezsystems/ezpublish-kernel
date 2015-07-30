<?php

/**
 * File containing the ReduceNoiseFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\ReduceNoiseFilterLoader;
use PHPUnit_Framework_TestCase;

class ReduceNoiseFilterLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filter;

    /**
     * @var ReduceNoiseFilterLoader
     */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->filter = $this->getMock('\eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\FilterInterface');
        $this->loader = new ReduceNoiseFilterLoader($this->filter);
    }

    /**
     * @expectedException \Imagine\Exception\NotSupportedException
     */
    public function testLoadInvalidDriver()
    {
        $this->loader->load($this->getMock('\Imagine\Image\ImageInterface'));
    }
}
