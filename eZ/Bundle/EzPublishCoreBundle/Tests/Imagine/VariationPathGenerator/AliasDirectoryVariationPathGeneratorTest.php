<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\VariationPathGenerator;

use eZ\Bundle\EzPublishCoreBundle\Imagine\VariationPathGenerator\AliasDirectoryVariationPathGenerator;
use PHPUnit\Framework\TestCase;

class AliasDirectoryVariationPathGeneratorTest extends TestCase
{
    public function testGetVariationPath()
    {
        $generator = new AliasDirectoryVariationPathGenerator();

        self::assertEquals(
            '_aliases/large/path/to/original.png',
            $generator->getVariationPath('path/to/original.png', 'large')
        );
    }
}
