<?php

/**
 * File containing the UnsupportedFilterTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\UnsupportedFilter;

class UnsupportedFilterTest extends AbstractFilterTest
{
    /**
     * @expectedException \Imagine\Exception\NotSupportedException
     */
    public function testLoad()
    {
        $filter = new UnsupportedFilter();
        $filter->apply($this->getMock('\Imagine\Image\ImageInterface'));
    }
}
