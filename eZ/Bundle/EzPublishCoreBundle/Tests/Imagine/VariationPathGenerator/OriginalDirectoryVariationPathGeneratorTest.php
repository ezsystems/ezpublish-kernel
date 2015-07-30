<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\VariationPathGenerator;

use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator\OriginalDirectoryVariationPathGenerator;

class OriginalDirectoryVariationPathGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetVariationPath()
    {
        $generator = new OriginalDirectoryVariationPathGenerator();
        self::assertEquals(
            'path/to/original_large.png',
            $generator->getVariationPath('path/to/original.png', 'large')
        );
    }
}
