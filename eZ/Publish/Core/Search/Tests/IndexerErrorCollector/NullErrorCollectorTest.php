<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Tests\IndexerErrorCollector;

use eZ\Publish\Core\Search\Common\IndexerErrorCollector\NullErrorCollector;
use eZ\Publish\Core\Search\Tests\TestCase;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;

/**
 * Test case for NullErrorCollector.
 *
 * @covers \eZ\Publish\Core\Search\Common\IndexerErrorCollector\NullErrorCollector
 */
class NullErrorCollectorTest extends TestCase
{
    public function testCollect()
    {
        $collector = new NullErrorCollector();

        foreach ($this->getContentInfos() as $contentInfo) {
            $collector->collect($contentInfo, 'Invalid data!');
        }

        $this->assertFalse($collector->hasErrors());
        $this->assertEquals(count($collector->getErrors()), 0);
    }

    public function testContinueOnError()
    {
        $collector = new NullErrorCollector();

        $this->assertFalse($collector->collect($this->getContentInfos()[0], 'Invalid data!'));
    }

    public function testNotContinueOnError()
    {
        $collector = new NullErrorCollector();

        $this->assertFalse($collector->collect($this->getContentInfos()[0], 'Invalid data!'));
    }

    private function getContentInfos()
    {
        return [
            new ContentInfo([
                'id' => 1,
            ]),
            new ContentInfo([
                'id' => 2,
            ]),
        ];
    }
}
