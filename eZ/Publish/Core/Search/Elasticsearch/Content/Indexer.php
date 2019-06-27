<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Search\Common\Indexer as SearchIndexer;
use eZ\Publish\Core\Search\Elasticsearch\Content\Handler as SearchHandler;
use PDO;
use RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ElasticSearch Indexer.
 *
 * @deprecated
 */
class Indexer extends SearchIndexer
{
    /** @var \eZ\Publish\Core\Search\Elasticsearch\Content\Handler */
    protected $searchHandler;

    /**
     * Create search engine index.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param int $iterationCount
     * @param bool $commit commit changes after each iteration
     */
    public function createSearchIndex(OutputInterface $output, $iterationCount, $commit)
    {
        $output->writeln('Creating Elasticsearch Search Engine Index...');

        if (!$this->searchHandler instanceof SearchHandler) {
            throw new RuntimeException(sprintf('Expected to find an instance of %s, but found %s', SearchHandler::class, get_class($this->searchHandler)));
        }

        $stmt = $this->getContentDbFieldsStmt(['count(id)']);
        $totalCount = (int) $stmt->fetchColumn();
        $stmt = $this->getContentDbFieldsStmt(['id', 'current_version']);
        $this->searchHandler->purgeIndex();
        /** @var \Symfony\Component\Console\Helper\ProgressBar $progress */
        $progress = new ProgressBar($output);
        $progress->start($totalCount);
        $i = 0;
        do {
            $contentObjects = [];
            for ($k = 0; $k <= $iterationCount; ++$k) {
                if (!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    break;
                }
                try {
                    $contentObjects[] = $this->persistenceHandler->contentHandler()->load(
                        $row['id'],
                        $row['current_version']
                    );
                } catch (NotFoundException $e) {
                    $this->logWarning($progress, "Could not load current version of Content with id ${row['id']}, so skipped for indexing. Full exception: " . $e->getMessage());
                }
            }
            foreach ($contentObjects as $contentObject) {
                try {
                    $this->searchHandler->indexContent($contentObject);
                    // Skip location indexing if search engine does not use it, or if content does not have locations
                    if (empty($contentObject->versionInfo->contentInfo->mainLocationId)) {
                        continue;
                    }
                    $this->indexLocations($contentObject->versionInfo->contentInfo->id, $progress);
                } catch (NotFoundException $e) {
                    $this->logWarning($progress, 'Content with id ' . $contentObject->versionInfo->id . ' has missing data, so skipped for indexing. Full exception: ' . $e->getMessage());
                }
            }
            if ($commit) {
                $this->searchHandler->flush();
            }
            $progress->advance($k);
        } while (($i += $iterationCount) < $totalCount);
        $progress->finish();
        $output->writeln('');

        $output->writeln('Finished creating Elasticsearch Search Engine Index');
    }

    /**
     * Index Locations of the given Content Object.
     *
     * @param int $contentId Content Object Id
     * @param \Symfony\Component\Console\Helper\ProgressBar $progress
     */
    private function indexLocations($contentId, ProgressBar $progress)
    {
        $locationIds = $this->getContentLocationIds($contentId);
        foreach ($locationIds as $locationId) {
            try {
                $location = $this->persistenceHandler->locationHandler()->load($locationId);
                $this->searchHandler->indexLocation($location);
            } catch (NotFoundException $e) {
                $this->logWarning($progress, "Could not load Location with id $locationId, so skipped for indexing. Full exception: " . $e->getMessage());
            }
        }
    }
}
