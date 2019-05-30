<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Tests\IndexerErrorCollector;

use eZ\Publish\Core\Search\Common\IndexerErrorCollector\LocalErrorCollector;
use eZ\Publish\Core\Search\Tests\TestCase;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;

/**
 * Test case for LocalErrorCollector.
 *
 * @covers \eZ\Publish\Core\Search\Common\IndexerErrorCollector\LocalErrorCollector
 */
class LocalErrorCollectorTest extends TestCase
{
    public function testCollect()
    {
        $collector = new LocalErrorCollector(false);

        foreach ($this->getContentInfos() as $contentInfo) {
            $collector->collect($contentInfo, 'Invalid data!');
        }

        $this->assertTrue($collector->hasErrors());
        $this->assertCount(count($collector->getErrors()), $this->getContentInfos());
    }

    public function testContinueOnError()
    {
        $collector = new LocalErrorCollector(true);

        $this->assertTrue($collector->collect($this->getContentInfos()[0], 'Invalid data!'));
    }

    public function testNotContinueOnError()
    {
        $collector = new LocalErrorCollector(false);

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
