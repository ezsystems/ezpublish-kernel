<?php

/**
 * File containing the ElasticsearchCreateIndexCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishElasticsearchSearchEngineBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use PDO;

/**
 * @deprecated since 6.7, use ezplatform:reindex command instead.
 */
class ElasticsearchCreateIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ezplatform:elasticsearch_create_index')
            ->setDescription('Indexes the configured database in configured Elasticsearch index')
            ->addArgument('bulk_count', InputArgument::OPTIONAL, 'Number of Content objects indexed at once', 5)
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> indexes current configured database in configured Elasticsearch index.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        @trigger_error(
            sprintf('%s is deprecated since 6.7. Use ezplatform:reindex command instead', $this->getName()),
            E_USER_DEPRECATED
        );

        $bulkCount = $input->getArgument('bulk_count');

        /** @var \eZ\Publish\SPI\Persistence\Handler $persistenceHandler */
        $persistenceHandler = $this->getContainer()->get('ezpublish.api.persistence_handler');
        /** @var \eZ\Publish\SPI\Search\Handler $searchHandler */
        $searchHandler = $this->getContainer()->get('ezpublish.spi.search');
        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $databaseHandler */
        $databaseHandler = $this->getContainer()->get('ezpublish.connection');

        // Indexing Content
        $query = $databaseHandler->createSelectQuery();
        $query->select('count(id)')
            ->from('ezcontentobject')
            ->where($query->expr->eq('status', ContentInfo::STATUS_PUBLISHED));
        $stmt = $query->prepare();
        $stmt->execute();
        $totalCount = $stmt->fetchColumn();

        $query = $databaseHandler->createSelectQuery();
        $query->select('id', 'current_version')
            ->from('ezcontentobject')
            ->where($query->expr->eq('status', ContentInfo::STATUS_PUBLISHED));

        $stmt = $query->prepare();
        $stmt->execute();

        /** @var \eZ\Publish\Core\Search\Elasticsearch\Content\Handler $searchHandler */
        $searchHandler->setCommit(true);
        $searchHandler->purgeIndex();

        $output->writeln('Indexing Content...');

        /** @var \Symfony\Component\Console\Helper\ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, $totalCount);
        $i = 0;
        do {
            $contentObjects = [];

            for ($k = 0; $k <= $bulkCount; ++$k) {
                if (!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    break;
                }

                $contentObjects[] = $persistenceHandler->contentHandler()->load(
                    $row['id'],
                    $row['current_version']
                );
            }

            if (!empty($contentObjects)) {
                $searchHandler->bulkIndexContent($contentObjects);
            }

            $progress->advance($k);
        } while (($i += $bulkCount) < $totalCount);

        $progress->finish();

        // Indexing Locations
        $query = $databaseHandler->createSelectQuery();
        $query
            ->select('count(node_id)')
            ->from('ezcontentobject_tree')
            ->where(
                $query->expr->neq(
                    $databaseHandler->quoteColumn('contentobject_id'),
                    $query->bindValue(0, null, PDO::PARAM_INT)
                )
            );
        $stmt = $query->prepare();
        $stmt->execute();
        $totalCount = $stmt->fetchColumn();

        $query = $databaseHandler->createSelectQuery();
        $query
            ->select('node_id')
            ->from('ezcontentobject_tree')
            ->where(
                $query->expr->neq(
                    $databaseHandler->quoteColumn('contentobject_id'),
                    $query->bindValue(0, null, PDO::PARAM_INT)
                )
            );

        $stmt = $query->prepare();
        $stmt->execute();

        $output->writeln('Indexing Locations...');

        /** @var \Symfony\Component\Console\Helper\ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, $totalCount);
        $i = 0;
        do {
            $locations = [];

            for ($k = 0; $k <= $bulkCount; ++$k) {
                if (!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    break;
                }

                $locations[] = $persistenceHandler->locationHandler()->load($row['node_id']);
            }

            if (!empty($locations)) {
                $searchHandler->bulkIndexLocations($locations);
            }

            $progress->advance($k);
        } while (($i += $bulkCount) < $totalCount);

        $progress->finish();
    }
}
