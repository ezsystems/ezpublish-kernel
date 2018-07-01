<?php

/**
 * File containing the class GlobCollectorTest.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Translation;

use eZ\Bundle\EzPublishCoreBundle\Translation\GlobCollector;
use PHPUnit\Framework\TestCase;

class GlobCollectorTest extends TestCase
{
    public function testCollect()
    {
        $translationRootDir = str_replace(
            sprintf('%1$sTests%1$sTranslation', DIRECTORY_SEPARATOR),
            sprintf('%1$sTests%1$sResources%1$sTranslation%1$svendor', DIRECTORY_SEPARATOR),
            __DIR__
        );
        $collector = new GlobCollector($translationRootDir);

        $files = $collector->collect();
        $this->assertEquals(3, count($files));
        foreach ($files as $file) {
            $this->assertTrue(in_array($file['domain'], ['messages', 'dashboard']));
            $this->assertTrue(in_array($file['locale'], ['fr', 'ach_UG']));
            $this->assertEquals($file['format'], 'xlf');
        }
    }
}
