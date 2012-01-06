<?php
/**
 * File containing the ContentHandler implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\InMemory;
use ezp\Persistence\Content\Handler as ContentHandlerInterface,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\UpdateStruct,
    ezp\Persistence\Content\RestrictedVersion,
    ezp\Persistence\Content\Query\Criterion,
    ezp\Persistence\Content\Query\Criterion\ContentId,
    ezp\Persistence\Content\Query\Criterion\Operator,
    ezp\Persistence\Content\FieldValue,
    ezp\Content,
    ezp\Content\Version,
    ezp\Base\Exception\NotFound,
    RuntimeException,
    ezp\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * @see ezp\Persistence\Content\Handler
 */
class ContentHandler implements ContentHandlerInterface
{
    /**
     * @var Handler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to Handler object that created it.
     *
     * @param Handler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( Handler $handler, Backend $backend )
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
                'typeId' => $content->typeId,
                'sectionId' => $content->sectionId,
                'ownerId' => $content->ownerId,
                'status' => Content::STATUS_DRAFT,
                'currentVersionNo' => 1,
                'modified' => $content->modified,
                'published' => $content->published,
            )
        );
        $time = time();
        $version = $this->backend->create(
            'Content\\Version',
            array(
                // @todo: Name should be computed!
                'name' => $content->name,
                'modified' => $time,
                'creatorId' => $content->ownerId,
                'created' => $time,
                'contentId' => $contentObj->id,
                'status' => Version::STATUS_DRAFT,
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
                    '_contentId' => $contentObj->id,
                    'value' => new FieldValue(
                        array(
                            'data' => $field->value->data,
                            'sortKey' => array( 'sort_key_string' => $field->value->sortKey )
                        )
                    )
                ) + (array)$field
            );
        }
        $contentObj->version = $version;

        $locationHandler = $this->handler->locationHandler();
        foreach ( $content->locations as $locationStruct )
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
    public function createDraftFromVersion( $contentId, $srcVersion )
    {
        $aVersion = $this->backend->find(
            'Content\\Version',
            array(
                'contentId' => $contentId,
                'versionNo' => $srcVersion
            )
        );
        if ( empty( $aVersion ) )
            throw new NotFound( "Version", "contentId: $contentId // versionNo: $srcVersion" );

        // Create new version
        $newVersionNo = $this->getLastVersionNumber( $contentId ) + 1;
        $time = time();
        $newVersion = $this->backend->create(
            'Content\\Version',
            array(
                'modified' => $time,
                // @todo: implement real user
                'creatorId' => $aVersion[0]->creatorId,
                'created' => $time,
                'contentId' => $contentId,
                'status' => Version::STATUS_DRAFT,
                'versionNo' => $newVersionNo
            )
        );

        // Duplicate fields
        // @todo: language support
        foreach (
            $this->backend->find(
                'Content\\Field',
                // Using internal _contentId since it's not directly exposed by Persistence
                array( '_contentId' => $contentId, 'versionNo' => $srcVersion )
            ) as $field
        )
        {
            $fieldVo = $this->backend->create(
                'Content\\Field',
                array( 'versionNo' => $newVersionNo, '_contentId' => $contentId ) + (array)$field
            );
            $newVersion->fields[] = $fieldVo;
        }

        return $newVersion;
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
                "typeId" => $content->typeId,
                "sectionId" => $content->sectionId,
                "ownerId" => $content->ownerId,
                "status" => $content->status,
                "currentVersionNo" => $currentVersionNo,
            )
        );

        $time = time();
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
                    "name" => $version->name,
                    "versionNo" => $version->versionNo,
                    "modified" => $time,
                    "creatorId" => $version->creatorId,
                    "created" => $time,
                    "contentId" => $contentObj->id,
                    "status" => $version->status,
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
        // @todo: Throw NotFound if no fields at all ?
        $contentObj->version->fields = $this->backend->find(
            "Content\\Field",
            array(
                "_contentId" => $contentObj->id,
                "versionNo" => $currentVersionNo
            )
        );

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
        if ( !isset( $versions[0] ) )
            throw new NotFound( "Version", "contentId:{$id}, versionNo:{$version}" );

        $versions[0]->fields = $this->backend->find(
            'Content\\Field',
            array(
                "_contentId" => $content->id,
                "versionNo" => $version
            )
        );

        $content->version = $versions[0];
        $content->locations = $this->backend->find( 'Content\\Location', array( 'contentId' => $content->id  ) );
        return $content;
    }

    /**
     * @see ezp\Persistence\Content\Handler
     */
    public function setStatus( $contentId, $status, $version )
    {
        $versions = $this->backend->find( 'Content\\Version', array( 'contentId' => $contentId, 'versionNo' => $version ) );

        if ( !count( $versions ) )
        {
            throw new NotFound( "Version", "contentId: $contentId, versionNo: $version" );
        }
        return $this->backend->update( 'Content\\Version', $versions[0]->id, array( 'status' => $status ) );
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
                "ownerId" => $content->ownerId,
            )
        );

        $this->backend->updateByMatch(
            'Content\\Version',
            array( 'contentId' => $content->id, 'versionNo' => $content->versionNo ),
            array(
                "name" => $content->name,
                "creatorId" => $content->creatorId,
                "modified" => $content->modified,
            )
        );
        foreach ( $content->fields as $field )
        {
            $this->backend->update(
                'Content\\Field',
                $field->id,
                (array)$field
            );
        }

        return $this->load( $content->id, $content->versionNo );
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
            $fields = $this->backend->find(
                'Content\\Field',
                array(
                    '_contentId' => $contentId,
                    'versionNo' => $version->versionNo
                )
            );
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

        // cast to RestrictedVersion
        foreach ( $versions as $key => $vo )
        {
            $restricted = new RestrictedVersion();
            foreach ( $restricted as $property => $value )
            {
                $restricted->$property = $vo->$property;
            }
            $versions[$key] = $restricted;
        }

        return $versions;
    }

    /**
     * Creates a relation between $sourceContentId in $sourceContentVersion
     * and $destinationContentId with a specific $type.
     *
     * @param  \ezp\Persistence\Content\Relation\CreateStruct $relation
     * @return \ezp\Persistence\Content\Relation
     */
    public function addRelation( RelationCreateStruct $relation )
    {
        // Ensure source content exists
        $sourceContent = $this->backend->find( "Content", array( "id" => $relation->sourceContentId ) );

        if ( empty( $sourceContent ) )
            throw new NotFound( "Content", "id: {$relation->sourceContentId}" );

        // Ensure source content exists if version is specified
        if ( $relation->sourceContentVersion !== null )
        {
            $version = $this->backend->find( "Content\\Version", array( "contentId" => $relation->sourceContentId, "versionNo" => $relation->sourceContentVersion ) );

            if ( empty( $version ) )
                throw new NotFound( "Content\\Version", "contentId: {$relation->sourceContentId}, versionNo: {$relation->sourceContentVersion}" );
        }

        // Ensure destination content exists
        $destinationContent = $this->backend->find( "Content", array( "id" => $relation->destinationContentId ) );

        if ( empty( $destinationContent ) )
            throw new NotFound( "Content", "id: {$relation->destinationContentId}" );

        return $this->backend->create( "Content\\Relation", (array)$relation );
    }

    /**
     * Removes a relation by relation Id.
     *
     * @param mixed $relationId
     * @throws \ezp\Base\Exception\NotFound if relation to be removed is not found.
     */
    public function removeRelation( $relationId )
    {
        $requestedRelation = $this->backend->find( "Content\\Relation", array( "id" => $relationId ) );
        if ( empty( $requestedRelation ) )
        {
            throw new NotFound( "Content\\Relation", "id: " . $relationId );
        }
        $this->backend->delete( "Content\\Relation", $relationId );
    }

    /**
     * Loads relations from $sourceContentId. Optionally, loads only those with $type and $sourceContentVersion.
     *
     * @param mixed $sourceContentId Source Content ID
     * @param mixed|null $sourceContentVersion Source Content Version, null if not specified
     * @param int|null $type {@see \ezp\Content\Relation::COMMON, \ezp\Content\Relation::EMBED, \ezp\Content\Relation::LINK, \ezp\Content\Relation::ATTRIBUTE}
     * @return \ezp\Persistence\Content\Relation[]
     */
    public function loadRelations( $sourceContentId, $sourceContentVersion = null, $type = null )
    {
        $filter = array( "sourceContentId" => $sourceContentId );
        if ( $sourceContentVersion !== null )
            $filter["sourceContentVersion"] = $sourceContentVersion;

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

    /**
     * Loads relations from $contentId. Optionally, loads only those with $type.
     *
     * Only loads relations against published versions.
     *
     * @param mixed $destinationContentId Destination Content ID
     * @param int|null $type {@see \ezp\Content\Relation::COMMON, \ezp\Content\Relation::EMBED, \ezp\Content\Relation::LINK, \ezp\Content\Relation::ATTRIBUTE}
     * @return \ezp\Persistence\Content\Relation[]
     */
    public function loadReverseRelations( $destinationContentId, $type = null )
    {
        $filter = array( "destinationContentId" => $destinationContentId );
        $relations = $this->backend->find( "Content\\Relation", $filter );

        if ( $type === null )
            return $relations;

        foreach ( $relations as $key => $relation )
        {
            if ( ~$relation->type & $type )
            {
                unset( $relations[$key] );
            }
        }
        return $relations;
    }

    /**
     * Performs the publishing operations required to set the version identified by $updateStruct->versionNo and
     * $updateStruct->id as the published one.
     *
     * @param \ezp\Persistence\Content\UpdateStruct An UpdateStruct with id and versionNo
     *
     * @return \ezp\Persistence\Content The published Content
     */
    public function publish( UpdateStruct $updateStruct )
    {
        // Change the currentVersionNo to the published version
        $this->backend->update(
            "Content", $updateStruct->id,
            array(
                'currentVersionNo' => $updateStruct->versionNo,
            )
        );

        // Change the currentVersionNo to the published version
        $this->backend->updateByMatch(
            'Content\\Version',
            array( 'contentId' => $updateStruct->id, 'versionNo' => $updateStruct->versionNo ),
            array(
                "name" => $updateStruct->name,
                "creatorId" => $updateStruct->creatorId,
                "modified" => $updateStruct->modified,
            )
        );

        return $this->load( $updateStruct->id, $updateStruct->versionNo );
    }

    /**
     * Returns last version number for content identified by $contentId
     *
     * @param int $contentId
     * @return int
     */
    private function getLastVersionNumber( $contentId )
    {
        $versionNumbers = array();
        $allVersions = $this->backend->find(
            'Content\\Version',
            array(
                'contentId' => $contentId
            )
        );

        if ( empty( $allVersions ) )
            throw new NotFound( "Version", "contentId: $contentId" );

        foreach ( $allVersions as $version )
        {
            $versionNumbers[] = $version->versionNo;
        }

        return max( $versionNumbers );
    }
}
