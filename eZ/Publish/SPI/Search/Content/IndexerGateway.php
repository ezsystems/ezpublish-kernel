<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Search\Content;

use DateTimeInterface;
use Generator;

/**
 * @internal
 */
interface IndexerGateway
{
    /**
     * @throws \Doctrine\DBAL\Exception
     *
     * @return \Generator list of Content IDs for each iteration
     */
    public function getContentSince(DateTimeInterface $since, int $iterationCount): Generator;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countContentSince(DateTimeInterface $since): int;

    /**
     * @throws \Doctrine\DBAL\Exception
     *
     * @return \Generator list of Content IDs for each iteration
     */
    public function getContentInSubtree(string $locationPath, int $iterationCount): Generator;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countContentInSubtree(string $locationPath): int;

    /**
     * @throws \Doctrine\DBAL\Exception
     *
     * @return \Generator list of Content IDs for each iteration
     */
    public function getAllContent(int $iterationCount): Generator;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllContent(): int;
}
