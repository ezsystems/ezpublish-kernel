<?php

/**
 * File containing the GrayscaleFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\GrayscaleFilterLoader;
use PHPUnit_Framework_TestCase;

class GrayscaleFilterLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $image = $this->getMock('\Imagine\Image\ImageInterface');
        $effects = $this->getMock('\Imagine\Effects\EffectsInterface');
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
