<?php
/**
 * File containing the ElasticsearchCreateIndexCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishElasticsearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use PDO;

class ElasticsearchCreateIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName( 'ezpublish:elasticsearch_create_index' )
            ->setDescription( 'Indexes the configured database in configured Elasticsearch index' )
            ->addArgument( "bulk_count", InputArgument::OPTIONAL, "Number of Content objects indexed at once", 5 )
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> indexes current configured database in configured Solr storage.
EOT
            );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $bulkCount = $input->getArgument( 'bulk_count' );

        /** @var \eZ\Publish\SPI\Persistence\Handler $persistenceHandler */
        $persistenceHandler = $this->getContainer()->get( 'ezpublish.api.persistence_handler' );
        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $databaseHandler */
        $databaseHandler = $this->getContainer()->get( 'ezpublish.connection' );

        $query = $databaseHandler
            ->createSelectQuery()
            ->select( "count(id)" )
            ->from( 'ezcontentobject' );
        $stmt = $query->prepare();
        $stmt->execute();
        $totalCount = $stmt->fetchColumn();

        $query = $databaseHandler
            ->createSelectQuery()
            ->select( 'id', 'current_version' )
            ->from( 'ezcontentobject' );

        $stmt = $query->prepare();
        $stmt->execute();

        /** @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Handler $searchHandler */
        $searchHandler = $persistenceHandler->searchHandler();
        $searchHandler->setCommit( false );
        $searchHandler->purgeIndex();
        $searchHandler->setCommit( true );

        /** @var \Symfony\Component\Console\Helper\ProgressHelper $progress */
        $progress = $this->getHelperSet()->get( 'progress' );
        $progress->start( $output, $totalCount );
        $i = 0;
        do
        {
            $contentObjects = array();

            for ( $k = 0; $k <= $bulkCount; $k++ )
            {
                if ( !$row = $stmt->fetch( PDO::FETCH_ASSOC ) )
                {
                    break;
                }

                $contentObjects[] = $persistenceHandler->contentHandler()->load(
                    $row['id'],
                    $row['current_version']
                );
            }

            if ( !empty( $contentObjects ) )
            {
                $searchHandler->bulkIndexContent( $contentObjects );
            }

            $progress->advance( $k );
        }
        while ( ( $i += $bulkCount ) < $totalCount );

        $progress->finish();
    }
}
