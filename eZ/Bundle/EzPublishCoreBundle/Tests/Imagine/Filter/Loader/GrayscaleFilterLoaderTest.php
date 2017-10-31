<?php

/**
 * File containing the GrayscaleFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\GrayscaleFilterLoader;
use PHPUnit\Framework\TestCase;

class GrayscaleFilterLoaderTest extends TestCase
{
    public function testLoad()
    {
        $image = $this->createMock('\Imagine\Image\ImageInterface');
        $effects = $this->createMock('\Imagine\Effects\EffectsInterface');
        $image
            ->expects($this->once())
            ->method('effects')
            ->will($this->returnValue($effects));
        $effects
            ->expects($this->once())
            ->method('grayscale')
            ->will($this->returnValue($effects));

        $loader = new GrayscaleFilterLoader();
        $this->assertSame($image, $loader->load($image));
    }
}
