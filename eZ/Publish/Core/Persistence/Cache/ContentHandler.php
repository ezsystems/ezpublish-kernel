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
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;
use eZ\Publish\Core\Persistence\Factory as PersistenceFactory;
use Tedivm\StashBundle\Service\CacheService;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;
use DOMDocument;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Handler
 */
class ContentHandler implements ContentHandlerInterface
{
    const FIELD_VALUE_DOM_DOCUMENT_KEY = '§:DomDocument:§';
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
    public function create( CreateStruct $struct )
    {
        // Cached on demand when published or loaded
        $this->logger->logCall( __METHOD__, array( 'struct' => $struct ) );
        return $this->persistenceFactory->getContentHandler()->create( $struct );
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
    public function load( $contentId, $version, $translations = null )
    {
        if ( $translations !== null )
        {
            $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'version' => $version, 'translations' => $translations ) );
            return $this->persistenceFactory->getContentHandler()->load( $contentId, $version, $translations );
        }

        $cache = $this->cache->get( 'content', $contentId, $version );
        $content = $cache->get();
        if ( $cache->isMiss() )
        {
            $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'version' => $version ) );
            $content = $this->persistenceFactory->getContentHandler()->load( $contentId, $version );
            $cache->set( $this->cloneAndSerializeXMLFields( $content ) );
        }
        else
        {
            $this->unSerializeXMLFields( $content );
        }

        return $content;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::loadContentInfo
     */
    public function loadContentInfo( $contentId )
    {
        $cache = $this->cache->get( 'content', 'info', $contentId );
        $contentInfo = $cache->get();
        if ( $cache->isMiss() )
        {
            $this->logger->logCall( __METHOD__, array( 'content' => $contentId ) );
            $cache->set( $contentInfo = $this->persistenceFactory->getContentHandler()->loadContentInfo( $contentId ) );
        }
        return $contentInfo;
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
        $return = $this->persistenceFactory->getContentHandler()->setStatus( $contentId, $status, $version );

        $this->cache->clear( 'content', $contentId, $version );
        if ( $status === VersionInfo::STATUS_PUBLISHED )
            $this->cache->clear( 'content', 'info', $contentId );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::updateMetadata
     */
    public function updateMetadata( $contentId, MetadataUpdateStruct $struct )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'struct' => $struct ) );

        $this->cache
            ->get( 'content', 'info', $contentId )
            ->set( $contentInfo = $this->persistenceFactory->getContentHandler()->updateMetadata( $contentId, $struct ) );

        return $contentInfo;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::updateContent
     */
    public function updateContent( $contentId, $versionNo, UpdateStruct $struct )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'version' => $versionNo, 'struct' => $struct ) );
        $content = $this->persistenceFactory->getContentHandler()->updateContent( $contentId, $versionNo, $struct );
        $this->cache
            ->get( 'content', $contentId, $versionNo )
            ->set( $this->cloneAndSerializeXMLFields( $content ) );
        return $content;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::deleteContent
     */
    public function deleteContent( $contentId )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId ) );
        $return = $this->persistenceFactory->getContentHandler()->deleteContent( $contentId );

        $this->cache->clear( 'content', $contentId );
        $this->cache->clear( 'content', 'info', $contentId );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::deleteVersion
     */
    public function deleteVersion( $contentId, $versionNo )
    {
        $this->logger->logCall( __METHOD__, array( 'content' => $contentId, 'version' => $versionNo ) );
        $return = $this->persistenceFactory->getContentHandler()->deleteVersion( $contentId, $versionNo );

        $this->cache->clear( 'content', $contentId, $versionNo );
        $this->cache->clear( 'content', 'info', $contentId );

        return $return;
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
        $content = $this->persistenceFactory->getContentHandler()->publish( $contentId, $versionNo, $struct );

        $this->cache->clear( 'content', $contentId );

        // warm up cache
        $contentInfo = $content->versionInfo->contentInfo;
        $this->cache
            ->get( 'content', $contentInfo->id, $content->versionInfo->versionNo )
            ->set( $this->cloneAndSerializeXMLFields( $content ) );
        $this->cache->get( 'content', 'info', $contentInfo->id )->set( $contentInfo );

        return $content;
    }

    /**
     * Custom serializer for Content
     *
     * Needed for DomDocuments on field values as they can not be serialized directly.
     *
     * @param Content $content
     * @return Content A serializable version of Content
     */
    protected function cloneAndSerializeXMLFields( Content $content )
    {
        $contentClone = clone $content;
        foreach ( $contentClone->fields as $key => $field )
        {
            $contentClone->fields[$key] = $fieldClone = clone $field;
            $fieldClone->value = clone $fieldClone->value;
            if ( $fieldClone->value->data instanceof DOMDocument )
            {
                $fieldClone->value->data =
                    self::FIELD_VALUE_DOM_DOCUMENT_KEY .
                        $fieldClone->value->data->saveXML();
            }

            if ( $fieldClone->value->externalData instanceof DOMDocument )
            {
                $fieldClone->value->externalData =
                    self::FIELD_VALUE_DOM_DOCUMENT_KEY .
                        $fieldClone->value->externalData->saveXML();
            }
        }
        return $contentClone;
    }

    /**
     * Custom unSerializer for Content
     *
     * Needed for DomDocuments on field values as they can not be serialized directly.
     *
     * @param Content $content
     * @return Content
     */
    protected function unSerializeXMLFields( Content $content )
    {
        foreach ( $content->fields as $field )
        {
            if ( !empty( $field->value->data ) &&
                is_string( $field->value->data ) &&
                strpos( $field->value->data, self::FIELD_VALUE_DOM_DOCUMENT_KEY ) === 0 )
            {
                $dom = new DOMDocument('1.0', 'UTF-8');
                $dom->loadXML( substr( $field->value->data, strlen( self::FIELD_VALUE_DOM_DOCUMENT_KEY ) ) );
                $field->value->data = $dom;
            }

            if ( !empty( $field->value->externalData ) &&
                is_string( $field->value->externalData ) &&
                strpos( $field->value->externalData, self::FIELD_VALUE_DOM_DOCUMENT_KEY ) === 0 )
            {
                $dom = new DOMDocument('1.0', 'UTF-8');
                $dom->loadXML( substr( $field->value->externalData, strlen( self::FIELD_VALUE_DOM_DOCUMENT_KEY ) ) );
                $field->value->externalData = $dom;
            }
        }
        return $content;
    }
}
