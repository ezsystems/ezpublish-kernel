<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacySearchEngineBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Search\Legacy\Content\Handler as SearchHandler;
use RuntimeException;
use PDO;

/**
 * Console command ezplatform:create_sql_search_index indexes content objects for legacy search
 * engine.
 */
class CreateIndexCommand extends ContainerAwareCommand
{
    /**
     * @var \eZ\Publish\Core\Search\Legacy\Content\Handler
     */
    private $searchHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    private $persistenceHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    private $databaseHandler;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Initialize objects required by {@see execute()}.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->logger = $this->getContainer()->get('logger');
        $this->searchHandler = $this->getContainer()->get('ezpublish.spi.search');
        $this->persistenceHandler = $this->getContainer()->get('ezpublish.api.persistence_handler');
        $this->databaseHandler = $this->getContainer()->get('ezpublish.connection');

        if (!$this->searchHandler instanceof SearchHandler) {
            throw new RuntimeException(
                'Expected to find Legacy Search Engine but found something else.' .
                "Did you forget to configure the repository with 'legacy' search engine?"
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ezplatform:create_sql_search_index')
            ->setDescription('Indexes the configured database for the legacy search engine')
            ->addArgument('bulk_count', InputArgument::OPTIONAL, 'Number of Content objects indexed at once', 5)
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> indexes content objects for the legacy search engine.
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bulkCount = $input->getArgument('bulk_count');
        // Indexing Content
        $totalCount = $this->getContentObjectsTotalCount(
            $this->databaseHandler, ContentInfo::STATUS_PUBLISHED
        );

        $query = $this->databaseHandler->createSelectQuery();
        $query->select('id', 'current_version')
            ->from('ezcontentobject')
            ->where($query->expr->eq('status', ContentInfo::STATUS_PUBLISHED));

        $stmt = $query->prepare();
        $stmt->execute();

        $this->searchHandler->purgeIndex();

        $output->writeln('Indexing Content...');

        /* @var \Symfony\Component\Console\Helper\ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, $totalCount);
        $i = 0;
        do {
            $contentObjects = [];
            for ($k = 0; $k <= $bulkCount; ++$k) {
                if (!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    break;
                }
                try {
                    $contentObjects[] = $this->persistenceHandler->contentHandler()->load(
                        $row['id'],
                        $row['current_version']
                    );
                } catch (NotFoundException $e) {
                    $this->logWarning($output, $progress, "Could not load current version of Content with id ${row['id']}, so skipped for indexing. Full exception: " . $e->getMessage());
                }
            }

            $this->searchHandler->bulkIndex(
                $contentObjects,
                function (Content $content, NotFoundException $e) use ($output, $progress) {
                    $this->logWarning($output, $progress, 'Content with id ' . $content->versionInfo->id . ' has missing data, so skipped for indexing. Full exception: ' . $e->getMessage()
                    );
                }
            );

            $progress->advance($k);
        } while (($i += $bulkCount) < $totalCount);

        $progress->finish();
    }

    /**
     * Get content objects total count.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $databaseHandler
     * @param int $contentObjectStatus ContentInfo constant
     *
     * @return int
     */
    private function getContentObjectsTotalCount(DatabaseHandler $databaseHandler, $contentObjectStatus)
    {
        $query = $databaseHandler->createSelectQuery();
        $query->select('count(id)')
            ->from('ezcontentobject')
            ->where($query->expr->eq('status', $contentObjectStatus));
        $stmt = $query->prepare();
        $stmt->execute();
        $totalCount = $stmt->fetchColumn();

        return $totalCount;
    }

    /**
     * Log warning while progress helper is running.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\ProgressHelper $progress
     * @param $message
     */
    private function logWarning(OutputInterface $output, ProgressHelper $progress, $message)
    {
        $progress->clear();
        // get rid of padding (side effect of displaying progress bar)
        $output->write("\r");
        $this->logger->warning($message);
        $progress->display();
    }
}
