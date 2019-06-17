<?php

/**
 * File containing the UnsupportedFilterTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\UnsupportedFilter;
use Imagine\Image\ImageInterface;

class UnsupportedFilterTest extends AbstractFilterTest
{
    /**
     */
    public function testLoad()
    {
        $this->expectException(\Imagine\Exception\NotSupportedException::class);

        $filter = new UnsupportedFilter();
        $filter->apply($this->createMock(ImageInterface::class));
    }
}
