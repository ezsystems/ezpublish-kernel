<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\AbstractRandom;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\Factory\RandomSortClauseHandlerFactory;
use PHPUnit\Framework\TestCase;

class RandomSortClauseHandlerFactoryTest extends TestCase
{
    /**
     * @dataProvider getGateways
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\AbstractRandom[] $gateways
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function testGetGateway(array $gateways)
    {
        $platform = $this->createMock(AbstractPlatform::class);

        $platform
            ->method('getName')
            ->willReturn('testStorage');

        $connection = $this->createMock(Connection::class);

        $connection
            ->method('getDatabasePlatform')
            ->willReturn($platform);

        $handlerFactory = new RandomSortClauseHandlerFactory($connection, $gateways);
        $this->assertEquals(
            'testStorage',
            $handlerFactory->getGateway()->getDriverName()
        );
    }

    /**
     * @dataProvider getGateways
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseHandler\AbstractRandom[] $gateways
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function testGetGatewayNotImplemented(array $gateways)
    {
        $platform = $this->createMock(AbstractPlatform::class);

        $platform
            ->method('getName')
            ->willReturn('notImplemented');

        $connection = $this->createMock(Connection::class);

        $connection
            ->method('getDatabasePlatform')
            ->willReturn($platform);

        $handlerFactory = new RandomSortClauseHandlerFactory($connection, $gateways);

        $this->expectException(InvalidArgumentException::class);
        $handlerFactory->getGateway();
    }

    public function getGateways(): array
    {
        $goodGateway = $this
            ->createMock(AbstractRandom::class);
        $goodGateway
            ->method('getDriverName')
            ->willReturn('testStorage');

        $badGateway = $this
            ->createMock(AbstractRandom::class);
        $badGateway
            ->method('getDriverName')
            ->willReturn('otherStorage');

        return [
            [
                [
                    $goodGateway,
                    $badGateway,
                ],
            ],
        ];
    }
}
