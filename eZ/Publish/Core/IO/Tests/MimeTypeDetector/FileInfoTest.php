<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\Tests\MimeTypeDetector;

use eZ\Publish\Core\IO\MimeTypeDetector\FileInfo as MimeTypeDetector;
use PHPUnit\Framework\TestCase;

class FileInfoTest extends TestCase
{
    /** @var MimeTypeDetector */
    protected $mimeTypeDetector;

    public function setUp()
    {
        $this->mimeTypeDetector = new MimeTypeDetector();
    }

    protected function getFixture()
    {
        return __DIR__ . '/../_fixtures/squirrel-developers.jpg';
    }

    public function testGetFromPath()
    {
        self::assertEquals(
            $this->mimeTypeDetector->getFromPath(
                $this->getFixture()
            ),
            'image/jpeg'
        );
    }

    public function testGetFromBuffer()
    {
        self::assertEquals(
            $this->mimeTypeDetector->getFromBuffer(
                file_get_contents($this->getFixture())
            ),
            'image/jpeg'
        );
    }
}
