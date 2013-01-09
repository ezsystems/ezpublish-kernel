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
     * Setups current handler with everything needed
     *
     * @param \Tedivm\StashBundle\Service\CacheService $cache
     * @param \eZ\Publish\Core\Persistence\Factory $persistenceFactory
     */
    public function __construct( CacheService $cache, PersistenceFactory $persistenceFactory )
    {
        $this->cache = $cache;
        $this->persistenceFactory = $persistenceFactory;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::createGroup
     */
    public function createGroup( GroupCreateStruct $group )
    {
        return $this->persistenceFactory->getContentTypeHandler()->createGroup( $group );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::updateGroup
     */
    public function updateGroup( GroupUpdateStruct $group )
    {
        return $this->persistenceFactory->getContentTypeHandler()->updateGroup( $group );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::deleteGroup
     */
    public function deleteGroup( $groupId )
    {
        return $this->persistenceFactory->getContentTypeHandler()->deleteGroup( $groupId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::loadGroup
     */
    public function loadGroup( $groupId )
    {
        return $this->persistenceFactory->getContentTypeHandler()->loadGroup( $groupId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::loadGroupByIdentifier
     */
    public function loadGroupByIdentifier( $identifier )
    {
        return $this->persistenceFactory->getContentTypeHandler()->loadGroupByIdentifier( $identifier );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::loadAllGroups
     */
    public function loadAllGroups()
    {
        return $this->persistenceFactory->getContentTypeHandler()->loadAllGroups();
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::loadContentTypes
     */
    public function loadContentTypes( $groupId, $status = Type::STATUS_DEFINED )
    {
        return $this->persistenceFactory->getContentTypeHandler()->loadContentTypes( $groupId, $status );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::load
     */
    public function load( $contentTypeId, $status = Type::STATUS_DEFINED )
    {
        if ( $status !== Type::STATUS_DEFINED )
            return $this->persistenceFactory->getContentTypeHandler()->load( $contentTypeId, $status );

        // Get cache for published content types
        $cache = $this->cache->get( 'contentType', $contentTypeId );
        if ( $cache->isMiss() )
            $cache->set( $type = $this->persistenceFactory->getContentTypeHandler()->load( $contentTypeId, $status ) );
        else
            $type = $cache->get();

        return $type;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::loadByIdentifier
     */
    public function loadByIdentifier( $identifier )
    {
        return $this->persistenceFactory->getContentTypeHandler()->loadByIdentifier( $identifier );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::loadByRemoteId
     */
    public function loadByRemoteId( $remoteId )
    {
        return $this->persistenceFactory->getContentTypeHandler()->loadByRemoteId( $remoteId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::create
     */
    public function create( CreateStruct $contentType )
    {
        $type = $this->persistenceFactory->getContentTypeHandler()->create( $contentType );

        if ( $type->status === Type::STATUS_DEFINED )
            $this->cache->get( 'contentType', $type->id )->set( $type );

        return $type;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::update
     */
    public function update( $typeId, $status, UpdateStruct $contentType )
    {
        if ( $status !== Type::STATUS_DEFINED )
            return $this->persistenceFactory->getContentTypeHandler()->update( $typeId, $status, $contentType );

        $this->cache
            ->get( 'contentType', $typeId )
            ->set( $type = $this->persistenceFactory->getContentTypeHandler()->update( $typeId, $status, $contentType ) );

        return $type;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::delete
     */
    public function delete( $contentTypeId, $status )
    {
        $return = $this->persistenceFactory->getContentTypeHandler()->delete( $contentTypeId, $status );

        if ( $status === Type::STATUS_DEFINED )
            $this->cache->clear( 'contentType', $contentTypeId );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::createDraft
     */
    public function createDraft( $modifierId, $contentTypeId )
    {
        return $this->persistenceFactory->getContentTypeHandler()->createDraft( $modifierId, $contentTypeId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::copy
     */
    public function copy( $userId, $contentTypeId, $status )
    {
        return $this->persistenceFactory->getContentTypeHandler()->copy( $userId, $contentTypeId, $status );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::unlink
     */
    public function unlink( $groupId, $contentTypeId, $status )
    {
        $return = $this->persistenceFactory->getContentTypeHandler()->unlink( $groupId, $contentTypeId, $status );

        if ( $status === Type::STATUS_DEFINED )
            $this->cache->clear( 'contentType', $contentTypeId );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::link
     */
    public function link( $groupId, $contentTypeId, $status )
    {
        $return = $this->persistenceFactory->getContentTypeHandler()->link( $groupId, $contentTypeId, $status );

        if ( $status === Type::STATUS_DEFINED )
            $this->cache->clear( 'contentType', $contentTypeId );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::getFieldDefinition
     */
    public function getFieldDefinition( $id, $status )
    {
        return $this->persistenceFactory->getContentTypeHandler()->getFieldDefinition( $id, $status );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::addFieldDefinition
     */
    public function addFieldDefinition( $contentTypeId, $status, FieldDefinition $fieldDefinition )
    {
        $return = $this->persistenceFactory->getContentTypeHandler()->addFieldDefinition(
            $contentTypeId,
            $status,
            $fieldDefinition
        );

        if ( $status === Type::STATUS_DEFINED )
            $this->cache->clear( 'contentType', $contentTypeId );

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::removeFieldDefinition
     */
    public function removeFieldDefinition( $contentTypeId, $status, $fieldDefinitionId )
    {
        $this->persistenceFactory->getContentTypeHandler()->removeFieldDefinition(
            $contentTypeId,
            $status,
            $fieldDefinitionId
        );

        if ( $status === Type::STATUS_DEFINED )
            $this->cache->clear( 'contentType', $contentTypeId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::updateFieldDefinition
     */
    public function updateFieldDefinition( $contentTypeId, $status, FieldDefinition $fieldDefinition )
    {
        $this->persistenceFactory->getContentTypeHandler()->updateFieldDefinition(
            $contentTypeId,
            $status,
            $fieldDefinition
        );

        if ( $status === Type::STATUS_DEFINED )
            $this->cache->clear( 'contentType', $contentTypeId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Type\Handler::publish
     */
    public function publish( $contentTypeId )
    {
        $this->persistenceFactory->getContentTypeHandler()->publish( $contentTypeId );
        $this->cache->clear( 'contentType', $contentTypeId );
    }
}
