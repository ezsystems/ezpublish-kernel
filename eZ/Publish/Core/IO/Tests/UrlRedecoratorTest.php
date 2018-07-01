<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Publish\Core\IO\Tests;

use PHPUnit\Framework\TestCase;
use eZ\Publish\Core\IO\UrlRedecorator;
use eZ\Publish\Core\IO\UrlDecorator;

class UrlRedecoratorTest extends TestCase
{
    /** @var UrlRedecorator|\PHPUnit\Framework\MockObject\MockObject */
    private $redecorator;

    /** @var \eZ\Publish\Core\IO\UrlDecorator|\PHPUnit\Framework\MockObject\MockObject */
    private $sourceDecoratorMock;

    /** @var \eZ\Publish\Core\IO\UrlDecorator|\PHPUnit\Framework\MockObject\MockObject */
    private $targetDecoratorMock;

    public function setUp()
    {
        $this->redecorator = new UrlRedecorator(
            $this->sourceDecoratorMock = $this->createMock(UrlDecorator::class),
            $this->targetDecoratorMock = $this->createMock(UrlDecorator::class)
        );
    }

    public function testRedecorateFromSource()
    {
        $this->sourceDecoratorMock
            ->expects($this->once())
            ->method('undecorate')
            ->with('http://static.example.com/images/file.png')
            ->will($this->returnValue('images/file.png'));

        $this->targetDecoratorMock
            ->expects($this->once())
            ->method('decorate')
            ->with('images/file.png')
            ->will($this->returnValue('/var/test/storage/images/file.png'));

        self::assertEquals(
            '/var/test/storage/images/file.png',
            $this->redecorator->redecorateFromSource('http://static.example.com/images/file.png')
        );
    }

    public function testRedecorateFromTarget()
    {
        $this->targetDecoratorMock
            ->expects($this->once())
            ->method('undecorate')
            ->with('/var/test/storage/images/file.png')
            ->will($this->returnValue('images/file.png'));

        $this->sourceDecoratorMock
            ->expects($this->once())
            ->method('decorate')
            ->with('images/file.png')
            ->will($this->returnValue('http://static.example.com/images/file.png'));

        self::assertEquals(
            'http://static.example.com/images/file.png',
            $this->redecorator->redecorateFromTarget('/var/test/storage/images/file.png')
        );
    }
}
