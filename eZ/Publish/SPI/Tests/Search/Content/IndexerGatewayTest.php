<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Tests\Search\Content;

use DateTimeImmutable;
use eZ\Publish\Core\Search\Legacy\Content\IndexerGateway;
use eZ\Publish\SPI\Tests\BaseGatewayTest;

/**
 * @internal
 *
 * @covers \eZ\Publish\Core\Search\Legacy\Content\IndexerGateway
 */
final class IndexerGatewayTest extends BaseGatewayTest
{
    /** @var \eZ\Publish\Core\Search\Legacy\Content\IndexerGateway */
    private $gateway;

    /**
     * @throws \ErrorException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new IndexerGateway($this->getRawDatabaseConnection());
    }

    public function getDataForContentSince(): iterable
    {
        yield '1999-01-01' => [
            new DateTimeImmutable('1999-01-01'),
            9,
            2,
        ];

        yield 'now' => [
            new DateTimeImmutable('now'),
            0,
            2,
        ];
    }

    public function getDataForContentInSubtree(): iterable
    {
        yield '/1/5/' => [
            '/1/5/',
            8,
            1,
        ];

        yield '/999/888/' => [
            '/999/888/',
            0,
            1,
        ];
    }

    /**
     * @dataProvider getDataForContentSince
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function testGetContentSince(
        DateTimeImmutable $since,
        int $expectedCount,
        int $iterationCount
    ): void {
        self::assertCount($expectedCount, iterator_to_array($this->gateway->getContentSince($since, $iterationCount)));
    }

    /**
     * @dataProvider getDataForContentSince
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function testCountContentSince(
        DateTimeImmutable $since,
        int $expectedCount,
        int $iterationCount
    ): void {
        self::assertSame(
            $expectedCount * $iterationCount,
            $this->gateway->countContentSince($since)
        );
    }

    /**
     * @dataProvider getDataForContentInSubtree
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function testGetContentInSubtree(
        string $subtreePath,
        int $expectedCount,
        int $iterationCount
    ): void {
        self::assertCount(
            $expectedCount,
            iterator_to_array($this->gateway->getContentInSubtree($subtreePath, $iterationCount))
        );
    }

    /**
     * @dataProvider getDataForContentInSubtree
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function testCountContentInSubtree(
        string $subtreePath,
        int $expectedCount,
        int $iterationCount
    ): void {
        self::assertSame(
            $expectedCount * $iterationCount,
            $this->gateway->countContentInSubtree($subtreePath)
        );
    }

    public function testCountAllContent(): void
    {
        self::assertCount(9, iterator_to_array($this->gateway->getAllContent(2)));
    }

    public function testGetAllContent(): void
    {
        self::assertSame(18, $this->gateway->countAllContent());
    }
}
