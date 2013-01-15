<?php
/**
 * File containing the ContentTypeHandler implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandlerInterface;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\Type\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct;
use eZ\Publish\Core\Persistence\Factory as PersistenceFactory;
use Tedivm\StashBundle\Service\CacheService;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler
 */
class ContentTypeHandler implements ContentTypeHandlerInterface
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
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::createGroup
     */
    public function createGroup( GroupCreateStruct $group )
    {
        $this->logger->logCall( __METHOD__, array( 'struct' => $group ) );
        return $this->persistenceFactory->getContentTypeHandler()->createGroup( $group );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::updateGroup
     */
    public function updateGroup( GroupUpdateStruct $group )
    {
        $this->logger->logCall( __METHOD__, array( 'struct' => $group ) );
        return $this->persistenceFactory->getContentTypeHandler()->updateGroup( $group );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::deleteGroup
     */
    public function deleteGroup( $groupId )
    {
        $this->logger->logCall( __METHOD__, array( 'group' => $groupId ) );
        return $this->persistenceFactory->getContentTypeHandler()->deleteGroup( $groupId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::loadGroup
     */
    public function loadGroup( $groupId )
    {
        $this->logger->logCall( __METHOD__, array( 'group' => $groupId ) );
        return $this->persistenceFactory->getContentTypeHandler()->loadGroup( $groupId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::loadGroupByIdentifier
     */
    public function loadGroupByIdentifier( $identifier )
    {
        $this->logger->logCall( __METHOD__, array( 'group' => $identifier ) );
        return $this->persistenceFactory->getContentTypeHandler()->loadGroupByIdentifier( $identifier );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::loadAllGroups
     */
    public function loadAllGroups()
    {
        $this->logger->logCall( __METHOD__ );
        return $this->persistenceFactory->getContentTypeHandler()->loadAllGroups();
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::loadContentTypes
     */
    public function loadContentTypes( $groupId, $status = Type::STATUS_DEFINED )
    {
        $this->logger->logCall( __METHOD__, array( 'group' => $groupId, 'status' => $status ) );
        return $this->persistenceFactory->getContentTypeHandler()->loadContentTypes( $groupId, $status );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::load
     */
    public function load( $typeId, $status = Type::STATUS_DEFINED )
    {
        if ( $status !== Type::STATUS_DEFINED )
        {
            $this->logger->logCall( __METHOD__, array( 'type' => $typeId, 'status' => $status ) );
            return $this->persistenceFactory->getContentTypeHandler()->load( $typeId, $status );
        }

        // Get cache for published content types
        $cache = $this->cache->get( 'contentType', $typeId );
        $type = $cache->get();
        if ( $cache->isMiss() )
        {
            $this->logger->logCall( __METHOD__, array( 'type' => $typeId, 'status' => $status ) );
            $cache->set( $type = $this->persistenceFactory->getContentTypeHandler()->load( $typeId, $status ) );
        }

        return $type;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::loadByIdentifier
     */
    public function loadByIdentifier( $identifier )
    {
        $this->logger->logCall( __METHOD__, array( 'type' => $identifier ) );
        return $this->persistenceFactory->getContentTypeHandler()->loadByIdentifier( $identifier );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::loadByRemoteId
     */
    public function loadByRemoteId( $remoteId )
    {
        $this->logger->logCall( __METHOD__, array( 'type' => $remoteId ) );
        return $this->persistenceFactory->getContentTypeHandler()->loadByRemoteId( $remoteId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::create
     */
    public function create( CreateStruct $contentType )
    {
        $this->logger->logCall( __METHOD__, array( 'struct' => $contentType ) );
        $type = $this->persistenceFactory->getContentTypeHandler()->create( $contentType );

        if ( $type->status === Type::STATUS_DEFINED )
            $this->cache->get( 'contentType', $type->id )->set( $type );

        return $type;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::update
     */
    public function update( $typeId, $status, UpdateStruct $struct )
    {
        $this->logger->logCall( __METHOD__, array( 'type' => $typeId, 'status' => $status, 'struct' => $struct ) );
        if ( $status !== Type::STATUS_DEFINED )
            return $this->persistenceFactory->getContentTypeHandler()->update( $typeId, $status, $struct );

        $this->cache
            ->get( 'contentType', $typeId )
            ->set( $type = $this->persistenceFactory->getContentTypeHandler()->update( $typeId, $status, $struct ) );

        return $type;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::delete
     */
    public function delete( $typeId, $status )
    {
        $this->logger->logCall( __METHOD__, array( 'type' => $typeId, 'status' => $status ) );
        $return = $this->persistenceFactory->getContentTypeHandler()->delete( $typeId, $status );

        if ( $status === Type::STATUS_DEFINED )
            $this->cache->clear( 'contentType', $typeId );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::createDraft
     */
    public function createDraft( $modifierId, $typeId )
    {
        $this->logger->logCall( __METHOD__, array( 'modifier' => $modifierId, 'type' => $typeId ) );
        return $this->persistenceFactory->getContentTypeHandler()->createDraft( $modifierId, $typeId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::copy
     */
    public function copy( $userId, $typeId, $status )
    {
        $this->logger->logCall( __METHOD__, array( 'user' => $userId, 'type' => $typeId, 'status' => $status ) );
        return $this->persistenceFactory->getContentTypeHandler()->copy( $userId, $typeId, $status );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::unlink
     */
    public function unlink( $groupId, $typeId, $status )
    {
        $this->logger->logCall( __METHOD__, array( 'group' => $groupId, 'type' => $typeId, 'status' => $status ) );
        $return = $this->persistenceFactory->getContentTypeHandler()->unlink( $groupId, $typeId, $status );

        if ( $status === Type::STATUS_DEFINED )
            $this->cache->clear( 'contentType', $typeId );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::link
     */
    public function link( $groupId, $typeId, $status )
    {
        $this->logger->logCall( __METHOD__, array( 'group' => $groupId, 'type' => $typeId, 'status' => $status ) );
        $return = $this->persistenceFactory->getContentTypeHandler()->link( $groupId, $typeId, $status );

        if ( $status === Type::STATUS_DEFINED )
            $this->cache->clear( 'contentType', $typeId );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::getFieldDefinition
     */
    public function getFieldDefinition( $id, $status )
    {
        $this->logger->logCall( __METHOD__, array( 'field' => $id, 'status' => $status ) );
        return $this->persistenceFactory->getContentTypeHandler()->getFieldDefinition( $id, $status );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::addFieldDefinition
     */
    public function addFieldDefinition( $typeId, $status, FieldDefinition $struct )
    {
        $this->logger->logCall( __METHOD__, array( 'type' => $typeId, 'status' => $status, 'struct' => $struct ) );
        $return = $this->persistenceFactory->getContentTypeHandler()->addFieldDefinition(
            $typeId,
            $status,
            $struct
        );

        if ( $status === Type::STATUS_DEFINED )
            $this->cache->clear( 'contentType', $typeId );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::removeFieldDefinition
     */
    public function removeFieldDefinition( $typeId, $status, $fieldDefinitionId )
    {
        $this->logger->logCall( __METHOD__, array( 'type' => $typeId, 'status' => $status, 'field' => $fieldDefinitionId ) );
        $this->persistenceFactory->getContentTypeHandler()->removeFieldDefinition(
            $typeId,
            $status,
            $fieldDefinitionId
        );

        if ( $status === Type::STATUS_DEFINED )
            $this->cache->clear( 'contentType', $typeId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::updateFieldDefinition
     */
    public function updateFieldDefinition( $typeId, $status, FieldDefinition $struct )
    {
        $this->logger->logCall( __METHOD__, array( 'type' => $typeId, 'status' => $status, 'struct' => $struct ) );
        $this->persistenceFactory->getContentTypeHandler()->updateFieldDefinition(
            $typeId,
            $status,
            $struct
        );

        if ( $status === Type::STATUS_DEFINED )
            $this->cache->clear( 'contentType', $typeId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::publish
     */
    public function publish( $typeId )
    {
        $this->logger->logCall( __METHOD__, array( 'type' => $typeId ) );
        $this->persistenceFactory->getContentTypeHandler()->publish( $typeId );
        $this->cache->clear( 'contentType', $typeId );
    }
}
