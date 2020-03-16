<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\SharedGateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms;
use eZ\Publish\Core\Persistence\Legacy\SharedGateway\DatabasePlatform\FallbackGateway;
use eZ\Publish\Core\Persistence\Legacy\SharedGateway\DatabasePlatform\SqliteGateway;
use eZ\Publish\Core\Persistence\Legacy\SharedGateway\GatewayFactory;
use PHPUnit\Framework\TestCase;
use Traversable;

/**
 * @covers \eZ\Publish\Core\Persistence\Legacy\SharedGateway\GatewayFactory
 */
final class GatewayFactoryTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\SharedGateway\GatewayFactory */
    private $factory;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setUp(): void
    {
        $gateways = [
            'sqlite' => new SqliteGateway($this->createMock(Connection::class)),
        ];

        $this->factory = new GatewayFactory(
            new FallbackGateway($this->createMock(Connection::class)),
            $gateways,
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\SharedGateway\GatewayFactory::buildSharedGateway
     *
     * @dataProvider getTestBuildSharedGatewayData
     *
     * @param \Doctrine\DBAL\Connection $connectionMock
     * @param string $expectedInstance
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testBuildSharedGateway(
        Connection $connectionMock,
        string $expectedInstance
    ): void {
        self::assertInstanceOf(
            $expectedInstance,
            $this->factory->buildSharedGateway($connectionMock)
        );
    }

    /**
     * @return \Doctrine\DBAL\Connection[]|\PHPUnit\Framework\MockObject\MockObject[]|\Traversable
     */
    public function getTestBuildSharedGatewayData(): Traversable
    {
        $databasePlatformGatewayPairs = [
            [new Platforms\SqlitePlatform(), SqliteGateway::class],
            [new Platforms\MySQL80Platform(), FallbackGateway::class],
            [new Platforms\MySqlPlatform(), FallbackGateway::class],
            [new Platforms\PostgreSqlPlatform(), FallbackGateway::class],
        ];

        foreach ($databasePlatformGatewayPairs as $databasePlatformGatewayPair) {
            [$databasePlatform, $sharedGateway] = $databasePlatformGatewayPair;
            /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $databasePlatform */
            $connectionMock = $this
                ->createMock(Connection::class);
            $connectionMock
                ->expects($this->any())
                ->method('getDatabasePlatform')
                ->willReturn($databasePlatform);

            yield [
                $connectionMock,
                $sharedGateway,
            ];
        }
    }
}
