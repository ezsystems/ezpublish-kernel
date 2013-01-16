<?php
/**
 * File containing the ContentHandler implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;
use eZ\Publish\Core\Persistence\Factory as PersistenceFactory;
use Tedivm\StashBundle\Service\CacheService;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Handler
 */
class ContentHandler implements ContentHandlerInterface
{
    /**
     * @var \Tedivm\StashBundle\Service\CacheService
     */
    protected $cache;

    /**
     * @var \eZ\Publish\Core\Persistence\Factory
     */
    protected $persistenceFactory;

    /**
     * @var PersistenceLogger
     */
    protected $logger;

    /**
     * Setups current handler with everything needed
     *
     * @param \Tedivm\StashBundle\Service\CacheService $cache
     * @param \eZ\Publish\Core\Persistence\Factory $persistenceFactory
     * @param PersistenceLogger $logger
     */
    public function __construct(
        CacheService $cache,
        PersistenceFactory $persistenceFactory,
        PersistenceLogger $logger )
    {
        $this->cache = $cache;
        $this->persistenceFactory = $persistenceFactory;
        $this->logger = $logger;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::create
     */
    public function create( CreateStruct $content )
    {
        $this->logger->logCall( __METHOD__, array( 'struct' => $content ) );
        return $this->persistenceFactory->getContentHandler()->create( $content );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::createDraftFromVersion
     */
    public function createDraftFromVersion( $contentId, $srcVersion, $userId )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'version' => $srcVersion, 'user' => $userId ) );
        return $this->persistenceFactory->getContentHandler()->createDraftFromVersion( $contentId, $srcVersion, $userId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::copy
     */
    public function copy( $contentId, $versionNo = null )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'version' => $versionNo ) );
        return $this->persistenceFactory->getContentHandler()->copy( $contentId, $versionNo );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::load
     */
    public function load( $id, $version, $translations = null )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $id, 'version' => $version, 'translations' => $translations ) );
        return $this->persistenceFactory->getContentHandler()->load( $id, $version, $translations );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::loadContentInfo
     */
    public function loadContentInfo( $contentId )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId ) );
        return $this->persistenceFactory->getContentHandler()->loadContentInfo( $contentId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::loadVersionInfo
     */
    public function loadVersionInfo( $contentId, $versionNo )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'version' => $versionNo ) );
        return $this->persistenceFactory->getContentHandler()->loadVersionInfo( $contentId, $versionNo );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::loadDraftsForUser
     */
    public function loadDraftsForUser( $userId )
    {
        $this->logger->logCall( __METHOD__, array( 'user' => $userId ) );
        return $this->persistenceFactory->getContentHandler()->loadDraftsForUser( $userId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::setStatus
     */
    public function setStatus( $contentId, $status, $version )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'status' => $status, 'version' => $version ) );
        return $this->persistenceFactory->getContentHandler()->setStatus( $contentId, $status, $version );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::updateMetadata
     */
    public function updateMetadata( $contentId, MetadataUpdateStruct $content )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'struct' => $content ) );
        return $this->persistenceFactory->getContentHandler()->updateMetadata( $contentId, $content );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::updateContent
     */
    public function updateContent( $contentId, $versionNo, UpdateStruct $content )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'version' => $versionNo, 'struct' => $content ) );
        return $this->persistenceFactory->getContentHandler()->updateContent( $contentId, $versionNo, $content );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::deleteContent
     */
    public function deleteContent( $contentId )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId ) );
        return $this->persistenceFactory->getContentHandler()->deleteContent( $contentId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::deleteVersion
     */
    public function deleteVersion( $contentId, $versionNo )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'version' => $versionNo ) );
        return $this->persistenceFactory->getContentHandler()->deleteVersion( $contentId, $versionNo );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::listVersions
     */
    public function listVersions( $contentId )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId ) );
        return $this->persistenceFactory->getContentHandler()->listVersions( $contentId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::addRelation
     */
    public function addRelation( RelationCreateStruct $relation )
    {
        $this->logger->logCall( __METHOD__, array( 'struct' => $relation ) );
        return $this->persistenceFactory->getContentHandler()->addRelation( $relation );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::removeRelation
     */
    public function removeRelation( $relationId )
    {
        $this->logger->logCall( __METHOD__, array( 'relation' => $relationId ) );
        return $this->persistenceFactory->getContentHandler()->removeRelation( $relationId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::loadRelations
     */
    public function loadRelations( $sourceContentId, $sourceContentVersionNo = null, $type = null )
    {
        $this->logger->logCall(
            __METHOD__,
            array(
                'content' => $sourceContentId,
                'version' => $sourceContentVersionNo,
                'type' => $type
            )
        );
        return $this->persistenceFactory->getContentHandler()->loadRelations( $sourceContentId, $sourceContentVersionNo, $type );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::loadReverseRelations
     */
    public function loadReverseRelations( $destinationContentId, $type = null )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $destinationContentId, 'type' => $type ) );
        return $this->persistenceFactory->getContentHandler()->loadReverseRelations( $destinationContentId, $type );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::publish
     */
    public function publish( $contentId, $versionNo, MetadataUpdateStruct $struct )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'version' => $versionNo, 'struct' => $struct ) );
        return $this->persistenceFactory->getContentHandler()->publish( $contentId, $versionNo, $struct );
    }
}
