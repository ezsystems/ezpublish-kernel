<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Search\Content;

use DateTime;
use Doctrine\DBAL\Driver\ResultStatement;
use Generator;

/**
 * @internal
 */
interface IndexerGateway
{
    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getStatementContentSince(DateTime $since, bool $count = false): ResultStatement;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getStatementSubtree(string $locationPath, bool $count = false): ResultStatement;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getStatementContentAll(bool $count = false): ResultStatement;

    /**
     * @return \Generator a list of Content IDs for each iteration
     */
    public function fetchIteration(ResultStatement $stmt, int $iterationCount): Generator;
}
