<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface for creating a search index in search handler.
 */
interface Indexing
{
    /**
     * Create search engine index.
     *
     * @param $bulkCount
     * @param \eZ\Publish\SPI\Search\IndexerDataProvider $dataProvider
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function createSearchIndex(
        $bulkCount,
        IndexerDataProvider $dataProvider,
        OutputInterface $output,
        LoggerInterface $logger
    );
}
