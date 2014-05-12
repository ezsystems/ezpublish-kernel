<?php
/**
 * File containing the Test Setup Factory base class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\SetupFactory;

use eZ\Publish\Core\Base\ServiceContainer;
use PDO;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
class LegacySolr extends Legacy
{
    /**
     * Solr server
     *
     * @var string
     */
    protected static $solrServer;

    /**
     * Creates a new setup factory
     */
    public function __construct()
    {
        self::$solrServer = getenv( "solrServer" );

        if ( !self::$solrServer )
        {
            self::$solrServer = "http://localhost:8983/";
        }

        parent::__construct();
    }

    /**
     * Returns a configured repository for testing.
     *
     * @param boolean $initializeFromScratch if the back end should be initialized
     *                                    from scratch or re-used
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository( $initializeFromScratch = true )
    {
        if ( $initializeFromScratch || !self::$schemaInitialized )
        {
            $this->initializeSchema();
            $this->insertData();
        }

        $this->clearInternalCaches();
        $repository = $this->getServiceContainer()->get( 'repository' );
        $repository->setCurrentUser(
            $repository->getUserService()->loadUser( 14 )
        );

        if ( $initializeFromScratch )
        {
            $this->indexAll();
        }

        return $repository;
    }

    protected function getServiceContainer()
    {
        if ( !isset( self::$serviceContainer ) )
        {
            $configManager = $this->getConfigurationManager();

            $serviceSettings = $configManager->getConfiguration( 'service' )->getAll();

            $serviceSettings['persistence_handler']['alias'] = 'persistence_handler_legacysolr';
            $serviceSettings['signal_dispatcher']['alias'] = 'legacysolr_signal_dispatcher';
            $serviceSettings['io_handler']['alias'] = 'io_handler_legacy';

            // Needed for URLAliasService tests
            $serviceSettings['inner_repository']['arguments']['service_settings']['language']['languages'][] = 'eng-US';
            $serviceSettings['inner_repository']['arguments']['service_settings']['language']['languages'][] = 'eng-GB';

            $serviceSettings['legacy_db_handler']['arguments']['dsn'] = self::$dsn;
            $serviceSettings['legacysolr_search_content_gateway_client_http_stream']['arguments']['server'] = self::$solrServer;

            self::$serviceContainer = new ServiceContainer(
                $serviceSettings,
                $this->getDependencyConfiguration()
            );
        }

        return self::$serviceContainer;
    }

    /**
     * Indexes all Content objects.
     */
    protected function indexAll()
    {
        // @todo: Is there a nicer way to get access to all content objects? We
        // require this to run a full index here.
        /** @var \eZ\Publish\SPI\Persistence\Handler $persistenceHandler */
        $persistenceHandler = $this->getServiceContainer()->get( 'persistence_handler_legacysolr' );
        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $databaseHandler */
        $databaseHandler = $this->getServiceContainer()->get( 'legacy_db_handler' );

        $query = $databaseHandler
            ->createSelectQuery()
            ->select( 'id', 'current_version' )
            ->from( 'ezcontentobject' );

        $stmt = $query->prepare();
        $stmt->execute();

        $contentObjects = array();
        while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) )
        {
            $contentObjects[] = $persistenceHandler->contentHandler()->load(
                $row['id'],
                $row['current_version']
            );
        }

        /** @var \eZ\Publish\Core\Persistence\Solr\Content\Search\Handler $searchHandler */
        $searchHandler = $persistenceHandler->searchHandler();
        $searchHandler->setCommit( false );
        $searchHandler->purgeIndex();
        $searchHandler->setCommit( true );
        $searchHandler->bulkIndexContent( $contentObjects );
    }
}
