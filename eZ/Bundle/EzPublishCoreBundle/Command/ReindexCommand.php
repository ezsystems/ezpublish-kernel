<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\Core\Search\Common\Indexer;
use eZ\Publish\Core\Search\Common\IncrementalIndexer;
use Doctrine\DBAL\Driver\Statement;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;
use RuntimeException;
use DateTime;
use PDO;

class ReindexCommand extends ContainerAwareCommand
{
    /**
     * @var \eZ\Publish\Core\Search\Common\Indexer|\eZ\Publish\Core\Search\Common\IncrementalIndexer
     */
    private $searchIndexer;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * Initialize objects required by {@see execute()}.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->searchIndexer = $this->getContainer()->get('ezpublish.spi.search.indexer');
        $this->connection = $this->getContainer()->get('ezpublish.api.storage_engine.legacy.connection');
        if (!$this->searchIndexer instanceof Indexer) {
            throw new RuntimeException(
                sprintf(
                    'Expected to find Search Engine Indexer but found "%s" instead',
                    get_parent_class($this->searchIndexer)
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ezplatform:reindex')
            ->setDescription('Recreate or Refresh search engine index')
            ->addOption(
                'iteration-count',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Number of objects to be indexed in a single iteration, for avoiding using too much memory',
                50
            )->addOption(
                'no-commit',
                null,
                InputOption::VALUE_NONE,
                'Do not commit after each iteration'
            )->addOption(
                'no-purge',
                null,
                InputOption::VALUE_NONE,
                'Do not purge before indexing'
            )->addOption(
                'since',
                null,
                InputOption::VALUE_OPTIONAL,
                'Refresh changes since a given time, any format understood by DateTime. Implies "no-purge", can not be combined with "content-ids" or "subtree"'
            )->addOption(
                'content-ids',
                null,
                InputOption::VALUE_OPTIONAL,
                'Comma separated list of content id\'s to refresh (deleted/updated/added). Implies "no-purge", can not be combined with "since" or "subtree"'
            )->addOption(
                'subtree',
                null,
                InputOption::VALUE_OPTIONAL,
                'Location Id to index subtree of (incl self). Implies "no-purge", can not be combined with "since" or "content-ids"'
            )->addOption(
                'processes',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of sub processes to spawn in parallel handling iterations, default number is number of CPU cores -1, set to 1 or 0 to disable',
                $this->getNumberOfCPUCores()
            )->setHelp(
                <<<EOT
The command <info>%command.name%</info> indexes current configured database in configured search engine index.


Example usage:
- Refresh (add/update) index changes since yesterday:
  <comment>ezplatform:reindex --since=yesterday</comment>
  See: http://php.net/manual/en/datetime.formats.php

- Refresh (add/update/remove) index on a set of content id's:
  <comment>ezplatform:reindex --content-ids=2,34,68</comment>

- Refresh (add/update) index of a subtree:
  <comment>ezplatform:reindex --subtree=45</comment>

 - Refresh (add/update) the whole index using 3 processes, & let search engine handle commits itself using auto commit:
   <comment>ezplatform:reindex --no-purge --no-commit --processes=3</comment>

EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commit = !$input->getOption('no-commit');
        $iterationCount = $input->getOption('iteration-count');
        if (!is_numeric($iterationCount) || (int) $iterationCount < 1) {
            throw new RuntimeException("'--iteration-count' option should be > 0, got '{$iterationCount}'");
        }

        if (!$this->searchIndexer instanceof IncrementalIndexer) {
            $output->writeln(<<<EOT
DEPRECATED:
Running indexing against an Indexer that has not been updated to use IncrementalIndexer abstract.

Options that won't be taken into account:
- since
- content-ids
- processes
- no-purge
EOT
            );
            $this->searchIndexer->createSearchIndex($output, (int) $iterationCount, !$commit);
        } else {
            $output->writeln('Re-indexing started for search engine: ' . $this->searchIndexer->getName());
            $output->writeln('');

            $return = $this->indexIncrementally($input, $output, $iterationCount, $commit);

            $output->writeln('');
            $output->writeln('Finished re-indexing');

            return $return;
        }
    }

    protected function indexIncrementally(InputInterface $input, OutputInterface $output, $iterationCount, $commit)
    {
        if ($contentIds = $input->getOption('content-ids')) {
            $output->writeln('Indexing list of content id\'s');

            return $this->searchIndexer->updateSearchIndex(explode(',', $contentIds), $commit);
        }

        if ($since = $input->getOption('since')) {
            $stmt = $this->getStatementContentSince(new DateTime($since));
            $count = (int)$this->getStatementContentSince(new DateTime($since), true)->fetchColumn();
            $purge = false;
        } elseif ($locationId = (int) $input->getOption('subtree')) {
            $stmt = $this->getStatementSubtree($locationId);
            $count = (int) $this->getStatementSubtree($locationId, true)->fetchColumn();
            $purge = false;
        } else {
            $stmt = $this->getStatementContentAll();
            $count = (int) $this->getStatementContentAll(true)->fetchColumn();
            $purge = !$input->getOption('no-purge');
        }

        if (!$count) {
            $output->writeln('<error>Could not find any items to index, aborting.</error>');

            return 1;
        }

        $iterations = ceil($count / $iterationCount);
        $processCount = (int) $input->getOption('processes');
        $processCount = $processCount > $iterations ? $iterations : $processCount;
        $processMessage = $processCount > 1 ? "using $processCount parallel processes" : 'using single process';

        if ($purge) {
            $output->writeln('Purging index...');
            $this->searchIndexer->purge();

            $output->writeln(
                "<info>Re-Creating index for {$count} items across $iterations iteration(s), $processMessage:"
            );
        } else {
            $output->writeln(
                "<info>Refreshing index for {$count} items across $iterations iteration(s), $processMessage:"
            );
        }

        $progress = new ProgressBar($output);
        $progress->start($iterations);

        if ($processCount > 1) {
            $this->runParallelProcess($progress, $stmt, (int) $processCount, (int) $iterationCount, $commit);
        } else {
            // if we only have one process, or less iterations to warrant running several, we index it all inline
            foreach ($this->fetchIteration($stmt, $iterationCount) as $contentIds) {
                $this->searchIndexer->updateSearchIndex($contentIds, $commit);
                $progress->advance(1);
            }
        }

        $progress->finish();
    }

    private function runParallelProcess(ProgressBar $progress, Statement $stmt, $processCount, $iterationCount, $commit)
    {
        /**
         * @var \Symfony\Component\Process\Process[]|null[]
         */
        $processes = array_fill(0, $processCount, null);
        $generator = $this->fetchIteration($stmt, $iterationCount);
        do {
            foreach ($processes as $key => $process) {
                if ($process !== null && $process->isRunning()) {
                    continue;
                }

                if ($process !== null) {
                    // One of the processes just finished, so we increment progress bar
                    $progress->advance(1);
                }

                if (!$generator->valid()) {
                    unset($processes[$key]);
                    continue;
                }

                $processes[$key] = $this->getPhpProcess($generator->current(), $commit);
                $processes[$key]->start();
                $generator->next();
            }

            if (!empty($processes)) {
                sleep(1);
            }
        } while (!empty($processes));
    }

    /**
     * @param DateTime $since
     * @param bool $count
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    private function getStatementContentSince(DateTime $since, $count = false)
    {
        $q = $this->connection->createQueryBuilder()
            ->select($count ? 'count(c.id)' : 'c.id')
            ->from('ezcontentobject', 'c')
            ->where('c.status = :status')->andWhere('c.modified >= :since')
            ->orderBy('c.modified')
            ->setParameter('status', ContentInfo::STATUS_PUBLISHED, PDO::PARAM_INT)
            ->setParameter('since', $since->getTimestamp(), PDO::PARAM_INT);

        return $q->execute();
    }

    /**
     * @param mixed $locationId
     * @param bool $count
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    private function getStatementSubtree($locationId, $count = false)
    {
        /**
         * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
         */
        $locationHandler = $this->getContainer()->get('ezpublish.spi.persistence.location_handler');
        $location = $locationHandler->load($locationId);
        $q = $this->connection->createQueryBuilder()
            ->select($count ? 'count(DISTINCT c.id)' : 'DISTINCT c.id')
            ->from('ezcontentobject', 'c')
            ->innerJoin('c', 'ezcontentobject_tree', 't', 't.contentobject_id = c.id')
            ->where('c.status = :status')
            ->andWhere('t.path_string LIKE :path')
            ->orderBy('t.path_string')
            ->setParameter('status', ContentInfo::STATUS_PUBLISHED, PDO::PARAM_INT)
            ->setParameter('path', $location->pathString . '%', PDO::PARAM_STR);

        return $q->execute();
    }

    /**
     * @param bool $count
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    private function getStatementContentAll($count = false)
    {
        $q = $this->connection->createQueryBuilder()
            ->select($count ? 'count(c.id)' : 'c.id')
            ->from('ezcontentobject', 'c')
            ->where('c.status = :status')
            ->setParameter('status', ContentInfo::STATUS_PUBLISHED, PDO::PARAM_INT);

        return $q->execute();
    }

    /**
     * @param \Doctrine\DBAL\Driver\Statement $stmt
     * @param int $iterationCount
     *
     * @return \Generator Return an array of arrays, each array contains content id's of $iterationCount.
     */
    private function fetchIteration(Statement $stmt, $iterationCount)
    {
        do {
            $contentIds = [];
            for ($i = 0; $i < $iterationCount; ++$i) {
                if ($contentId = $stmt->fetch(PDO::FETCH_COLUMN)) {
                    $contentIds[] = $contentId;
                } else {
                    break;
                }
            }

            yield $contentIds;
        } while (!empty($contentId));
    }

    /**
     * @param array $contentIds
     * @param bool $commit
     *
     * @return \Symfony\Component\Process\Process
     */
    private static function getPhpProcess(array $contentIds, $commit)
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find()) {
            throw new \RuntimeException(
                'The php executable could not be found, add it to your PATH environment variable and try again'
            );
        }

        $process = new ProcessBuilder([
            file_exists('bin/console') ? 'bin/console' : 'app/console',
            'ezplatform:reindex',
            '--content-ids=' . implode(',', $contentIds),
        ]);
        $process->setTimeout(null);
        $process->setPrefix($phpPath);

        if (!$commit) {
            $process->add('--no-commit');
        }

        return $process->getProcess();
    }

    /**
     * @return int
     */
    private function getNumberOfCPUCores()
    {
        $cores = 1;
        if (is_file('/proc/cpuinfo')) {
            // Linux (and potentially Windows with linux sub systems)
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $cores = count($matches[0]);
        } elseif (DIRECTORY_SEPARATOR === '\\') {
            // Windows
            if (($process = @popen('wmic cpu get NumberOfCores', 'rb')) !== false) {
                fgets($process);
                $cores = (int) fgets($process);
                pclose($process);
            }
        } elseif (($process = @popen('sysctl -a', 'rb')) !== false) {
            // *nix (Linux, BSD and Mac)
            $output = stream_get_contents($process);
            if (preg_match('/hw.ncpu: (\d+)/', $output, $matches)) {
                $cores = (int) $matches[1][0];
            }
            pclose($process);
        }

        return $cores;
    }
}
