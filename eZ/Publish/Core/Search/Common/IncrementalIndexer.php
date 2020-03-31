<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common;

use Doctrine\DBAL\FetchMode;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Base class for the Search Engine Indexer Service aimed to recreate Search Engine Index.
 * Each Search Engine has to extend it on its own.
 *
 * Extends indexer to allow for reindexing your install while it is in production by splitting indexing into tree tasks:
 * - Remove items in index no longer valid in database
 * - Making purge of index optional
 * - indexing by specifying id's, for purpose of supporting parallel indexing
 *
 * @api
 */
abstract class IncrementalIndexer extends Indexer
{
    /**
     * @deprecated Kept for compatibility with consumers of Indexer, performs purge first & recreate of index second.
     */
    final public function createSearchIndex(OutputInterface $output, $iterationCount, $commit)
    {
        $output->writeln('Re-creating search index for: ' . $this->getName());
        $output->writeln('Purging Index...');
        $this->searchHandler->purgeIndex();

        $stmt = $this->getContentDbFieldsStmt(['count(id)']);
        $totalCount = (int) ($stmt->fetchColumn());
        $stmt = $this->getContentDbFieldsStmt(['id']);

        $output->writeln("Re-creating search engine index for {$totalCount} content items...");
        $progress = new ProgressBar($output);
        $progress->start($totalCount);

        $i = 0;
        do {
            $contentIds = [];
            for ($k = 0; $k <= $iterationCount; ++$k) {
                if (!$row = $stmt->fetch(FetchMode::ASSOCIATIVE)) {
                    break;
                }

                $contentIds[] = $row['id'];
            }

            $this->updateSearchIndex($contentIds, $commit);

            $progress->advance($k);
        } while (($i += $iterationCount) < $totalCount);

        $progress->finish();
        $output->writeln('');
        $output->writeln('Finished creating Search Engine Index');
    }

    /**
     * Updates search engine index based on Content id's.
     *
     * If content is:
     * - deleted (NotFoundException)
     * - not published (draft or trashed)
     * Then item is removed from index, if not it is added/updated.
     *
     * If generic unhandled exception is thrown, then item indexing is skipped and warning is logged.
     *
     * @param int[] $contentIds
     * @param bool $commit
     */
    abstract public function updateSearchIndex(array $contentIds, $commit);

    /**
     * Purges whole index, should only be done if user asked for it.
     */
    abstract public function purge();

    /**
     * Return human readable name of given search engine (and if custom indexer you can append that to).
     *
     * @return string
     */
    abstract public function getName();
}
