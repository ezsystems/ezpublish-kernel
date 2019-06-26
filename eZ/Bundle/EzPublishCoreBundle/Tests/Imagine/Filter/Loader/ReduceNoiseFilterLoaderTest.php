<?php

/**
 * File containing the ReduceNoiseFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\FilterInterface;
use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\ReduceNoiseFilterLoader;
use Imagine\Image\ImageInterface;
use PHPUnit\Framework\TestCase;

class ReduceNoiseFilterLoaderTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $filter;

    /** @var ReduceNoiseFilterLoader */
    private $loader;

    protected function setUp()
    {
        parent::setUp();
        $this->filter = $this->createMock(FilterInterface::class);
        $this->loader = new ReduceNoiseFilterLoader($this->filter);
    }

    /**
     * @expectedException \Imagine\Exception\NotSupportedException
     */
    public function testLoadInvalidDriver()
    {
        $this->loader->load($this->createMock(ImageInterface::class));
    }
}
