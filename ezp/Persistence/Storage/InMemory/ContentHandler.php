<?php
/**
 * File containing the ContentHandler implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\InMemory;
use ezp\Persistence\Content\Handler as ContentHandlerInterface,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Criterion\ContentId,
    ezp\Persistence\Content\Criterion\Operator,
    ezp\Content\Version,
    ezp\Base\Exception\NotFound,
    RuntimeException;

/**
 * @see ezp\Persistence\Content\Handler
 */
class ContentHandler implements ContentHandlerInterface
{
    /**
     * @var RepositoryHandler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to RepositoryHandler object that created it.
     *
     * @param RepositoryHandler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( RepositoryHandler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function create( CreateStruct $content )
    {
        $contentObj = $this->backend->create(
            'Content', array(
                'name' => $content->name,
                'typeId' => $content->typeId,
                'sectionId' => $content->sectionId,
                'ownerId' => $content->ownerId,
                'currentVersionNo' => 1,
            )
        );
        $version = $this->backend->create(
            'Content\\Version',
            array(
                'modified' => time(),
                'creatorId' => $content->ownerId,
                'created' => time(),
                'contentId' => $contentObj->id,
                'state' => Version::STATUS_DRAFT,
                'versionNo' => 1
            )
        );
        foreach ( $content->fields as $field )
        {
            $version->fields[] = $this->backend->create(
                'Content\\Field',
                array(
                    'versionNo' => $version->versionNo,
                    // Using internal _contentId since it's not directly exposed by Persistence
                    '_contentId' => $contentObj->id
                ) + (array)$field
            );
        }
        $contentObj->version = $version;

        $locationHandler = $this->handler->locationHandler();
        foreach ( $content->parentLocations as $locationStruct )
        {
            $locationStruct->contentId = $contentObj->id;
            $locationStruct->contentVersion = $contentObj->currentVersionNo;
            $contentObj->locations[] = $locationHandler->create( $locationStruct );
        }
        return $contentObj;
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function createDraftFromVersion( $contentId, $srcVersion = false )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * Copy Content with Fields and Versions from $contentId in $version.
     *
     * Copies all fields from $contentId in $version (or all versions if false)
     * to a new object which is returned. Version numbers are maintained.
     *
     * @param int $contentId
     * @param int|false $versionNo Copy all versions if left false
     * @return \ezp\Persistence\Content
     * @throws \ezp\Base\Exception\NotFound If content or version is not found
     * @todo Language support
     */
    public function copy( $contentId, $versionNo )
    {
        $content = $this->backend->load( "Content", $contentId );
        if ( !$content )
            throw new NotFound( "Content", "contentId: $contentId" );

        $currentVersionNo = $versionNo === false ? $content->currentVersionNo : $versionNo;
        $contentObj = $this->backend->create(
            "Content", array(
                "name" => $content->name,
                "typeId" => $content->typeId,
                "sectionId" => $content->sectionId,
                "ownerId" => $content->ownerId,
                "currentVersionNo" => $currentVersionNo,
            )
        );

        // Copy version(s)
        foreach (
            $this->backend->find(
                "Content\\Version",
                $versionNo === false ?
                array( "contentId" => $content->id ) :
                array( "contentId" => $content->id, "versionNo" => $versionNo )
            ) as $version )
        {
            $this->backend->create(
                "Content\\Version",
                array(
                    "versionNo" => $version->versionNo,
                    "modified" => time(),
                    "creatorId" => $version->creatorId,
                    "created" => time(),
                    "contentId" => $contentObj->id,
                    "state" => $version->state,
                )
            );
        }

        // Associate last version to content VO
        $aVersion = $this->backend->find(
            'Content\\Version',
            array(
                'contentId' => $contentObj->id,
                'versionNo' => $currentVersionNo
            )
        );
        if ( empty( $aVersion ) )
            throw new NotFound( "Version", "contentId: $contentObj->id // versionNo: $currentVersionNo" );

        $contentObj->version = $aVersion[0];

        // Copy fields
        // @todo: language support
        foreach (
            $this->backend->find(
                "Content\\Field",
                // Using internal _contentId since it's not directly exposed by Persistence
                $versionNo === false ?
                array( "_contentId" => $content->id ) :
                array( "_contentId" => $content->id, "versionNo" => $versionNo )
            ) as $field
        )
        {
            $this->backend->create(
                'Content\\Field',
                array( '_contentId' => $contentObj->id ) + (array)$field
            );
        }

        // Associate last version's fields
        $aFields = $this->backend->find(
            'Content\\Field',
            array(
                '_contentId' => $contentObj->id,
                'versionNo' => $currentVersionNo
            )
        );
        // @todo: Throw NotFound if no fields at all ?
        $contentObj->version->fields = $aFields;

        return $contentObj;
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function load( $id, $version, $translations = null )
    {
        $content = $this->backend->load( 'Content', $id );
        if ( !$content )
            return null;

        $versions = $this->backend->find( 'Content\\Version', array( 'contentId' => $content->id, 'versionNo' => $version ) );
        $versions[0]->fields = $this->backend->find( 'Content\\Field', array( '_contentId' => $content->id,
                                                                              'versionNo' => $version ) );

        $content->version = $versions[0];
        // @todo Loading locations by content object id should be possible using handler API.
        $content->locations = $this->backend->find( 'Content\\Location', array( 'contentId' => $content->id  ) );
        return $content;
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function setState( $contentId, $state, $version )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function setObjectState( $contentId, $stateGroup, $state )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function getObjectState( $contentId, $stateGroup )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function update( UpdateStruct $content )
    {
        // @todo Assume the attached version to Content is the one that should be updated
        $this->backend->update(
            "Content",
            $content->id,
            array(
                "ownerId" => $content->userId,
                "currentVersionNo" => $content->versionNo,
                "name" => $content->name,
            )
        );

        // @todo update fields

        return $this->backend->load( "Content", $content->id );
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function delete( $contentId )
    {
        $this->backend->delete( "Content", $contentId );

        $versions = $this->backend->find( 'Content\\Version', array( 'contentId' => $contentId ) );
        foreach ( $versions as $version )
        {
            $fields = $this->backend->find( 'Content\\Field', array( '_contentId' => $contentId,
                                                                     'versionNo' => $version->versionNo ) );
            foreach ( $fields as $field )
                $this->backend->delete( 'Content\\Field', $field->id );

            $this->backend->delete( 'Content\\Version', $version->id );
        }

        // @todo Deleting Locations by content object id should be possible using handler API?
        $locationHandler = $this->handler->locationHandler();
        $locations = $this->backend->find( 'Content\\Location', array( 'contentId' => $contentId ) );
        foreach ( $locations as $location )
        {
            $locationHandler->removeSubtree( $location->id );
        }
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function trash( $contentId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function untrash( $contentId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Handler
     * @throws \ezp\Base\Exception\NotFound If no version found
     */
    public function listVersions( $contentId )
    {
        $versions = $this->backend->find( "Content\\Version", array( "contentId" => $contentId ) );

        if ( empty( $versions ) )
            throw new NotFound( "Content\\Version", "contentId: $contentId" );

        return $versions;
    }

    /**
     * @see ezp\Persistence\Content\Handler
     * @throws \ezp\Base\Exception\NotFound If no fields can be found
     */
    public function loadFields( $contentId, $version )
    {
        $fields = $this->backend->find(
            'Content\\Field',
            array(
                '_contentId' => $contentId,
                'versionNo' => $version
            )
        );

        if ( empty( $fields ) )
            throw new NotFound( "Content\\Field", "contentId: $contentId // versionNo: $version" );

        return $fields;
    }

    /**
     * Creates a relation between $sourceContentId in $sourceContentVersion
     * and $destinationContentId with a specific $type.
     *
     * @todo Handle Attribute relation
     * @todo Should the existence verifications happen here or is this supposed to be handled at a higher level?
     *
     * @param int $sourceContentId Source Content ID
     * @param int|null $sourceContentVersion Source Content Version, null if not specified
     * @param int $destinationContentId Destination Content ID
     * @param int $type {@see \ezp\Content\Relation::COMMON, \ezp\Content\Relation::EMBED, \ezp\Content\Relation::LINK, \ezp\Content\Relation::ATTRIBUTE}
     */
    public function addRelation( $sourceContentId, $sourceContentVersion, $destinationContentId, $type )
    {
        // Ensure source content exists
        $sourceContent = $this->backend->find( "Content", array( "id" => $sourceContentId ) );

        if ( empty( $sourceContent ) )
            throw new NotFound( "Content", "id: $sourceContentId" );

        // Ensure source content exists if version is specified
        if ( $sourceContentVersion !== null )
        {
            $version = $this->backend->find( "Content\\Version", array( "contentId" => $sourceContentId, "versionNo" => $sourceContentVersion ) );

            if ( empty( $version ) )
                throw new NotFound( "Content\\Version", "contentId: $sourceContentId, versionNo: $sourceContentVersion" );
        }

        // Ensure destination content exists
        $destinationContent = $this->backend->find( "Content", array( "id" => $destinationContentId ) );

        if ( empty( $destinationContent ) )
            throw new NotFound( "Content", "id: $destinationContentId" );

        return $this->backend->create(
            "Content\\Relation", array(
                "type" => $type,
                "sourceContentId" => $sourceContentId,
                "sourceContentVersion" => $sourceContentVersion,
                "destinationContentId" => $destinationContentId,
                "attributeId" => null,
            )
        );
    }

    /**
     * Loads relations from $contentId. Optionally, loads only those with $type.
     *
     * @param int $contentId Source Content ID
     * @param int|null $contentVersion Source Content Version, null if not specified
     * @param int|null $type {@see \ezp\Content\Relation::COMMON, \ezp\Content\Relation::EMBED, \ezp\Content\Relation::LINK, \ezp\Content\Relation::ATTRIBUTE}
     * @return \ezp\Persistence\Content\Relation[]
     */
    public function loadRelations( $contentId, $contentVersion = null, $type = null )
    {
        $filter = array( "sourceContentId" => $contentId );
        if ( $contentVersion !== null )
            $filter["sourceContentVersion"] = $contentVersion;

        $relations = $this->backend->find( "Content\\Relation", $filter );

        if ( $type === null )
            return $relations;

        foreach ( $relations as $key => $relation )
        {
            // Is there a bit present in $type not appearing in $relation->type?
            if ( ~$relation->type & $type )
            {
                // In such case, remove the result from the array
                unset( $relations[$key] );
            }
        }

        return $relations;
    }
}
