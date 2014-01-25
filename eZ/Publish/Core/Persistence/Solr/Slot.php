<?php
/**
 * File containing the Solr\Slot abstract class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr;

use eZ\Publish\Core\Repository\SignalSlot\Slot as BaseSlot;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\SPI\Persistence\Handler;

/**
 * General slot implementation for Solr slots
 */
abstract class Slot extends BaseSlot
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    public function __construct( Repository $repository, Handler $handler )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
    }

    /**
     * Enqueue the indexing of content
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    protected function enqueueIndexing( $content )
    {
        $searchHandler = $this->persistenceHandler->searchHandler();

        $this->repository->commitEvent(
            function ( $lastEvent ) use ( $searchHandler, $content )
            {
                $searchHandler->setCommit( $lastEvent );
                $searchHandler->indexContent( $content );
            }
        );
    }

    /**
     * Enqueue the deletion of content
     *
     * @param mixed $contentId
     * @param int|null $versionNo
     */
    protected function enqueueDeletion( $contentId, $versionNo = null )
    {
        $searchHandler = $this->persistenceHandler->searchHandler();

        $this->repository->commitEvent(
            function ( $lastEvent ) use ( $searchHandler, $contentId, $versionNo )
            {
                $searchHandler->setCommit( $lastEvent );
                $searchHandler->deleteContent( $contentId, $versionNo );
            }
        );
    }

    /**
     * Enqueue the deletion of location
     *
     * @param mixed $locationId
     */
    protected function enqueueDeletionLocation( $locationId )
    {
        $searchHandler = $this->persistenceHandler->searchHandler();

        $this->repository->commitEvent(
            function ( $lastEvent ) use ( $searchHandler, $locationId )
            {
                $searchHandler->setCommit( $lastEvent );
                $searchHandler->deleteLocation( $locationId );
            }
        );
    }
}
