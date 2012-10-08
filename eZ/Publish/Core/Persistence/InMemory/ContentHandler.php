<?php
/**
 * File containing the ContentHandler implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandlerInterface,
    eZ\Publish\SPI\Persistence\Content\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound,
    RuntimeException,
    eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * @see eZ\Publish\SPI\Persistence\Content\Handler
 */
class ContentHandler implements ContentHandlerInterface
{
    /**
     * @var \eZ\Publish\Core\Persistence\InMemory\Handler
     */
    protected $handler;

    /**
     * @var \eZ\Publish\Core\Persistence\InMemory\Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to Handler object that created it.
     *
     * @param \eZ\Publish\Core\Persistence\InMemory\Handler $handler
     * @param \eZ\Publish\Core\Persistence\InMemory\Backend $backend The storage engine backend
     */
    public function __construct( Handler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function create( CreateStruct $content )
    {
        /** @var \eZ\Publish\SPI\Persistence\Content $contentObj */
        $contentObj = $this->backend->create( 'Content', array( '_currentVersionNo' => 1 ) );
        /** @var \eZ\Publish\SPI\Persistence\Content\ContentInfo $contentInfo */
        $contentInfo = $this->backend->create(
            'Content\\ContentInfo',
            array(
                'contentTypeId' => $content->typeId,
                'sectionId' => $content->sectionId,
                'isPublished' => false,
                'ownerId' => $content->ownerId,
                'status' => VersionInfo::STATUS_DRAFT,
                'currentVersionNo' => 1,
                // Published and modified timestamps for drafts is 0
                'modificationDate' => 0,
                'publicationDate' => 0,
                'alwaysAvailable' => $content->alwaysAvailable,
                'remoteId' => $content->remoteId,
                'mainLanguageCode' => $this->handler->contentLanguageHandler()
                    ->load( $content->initialLanguageId )->languageCode
            ),
            true
        );
        $languageCodes = array();
        $languageIds = array();
        foreach ( $content->fields as $field )
        {
            $contentObj->fields[] = $this->backend->create(
                'Content\\Field',
                array(
                    'versionNo' => 1,
                    // Using internal _contentId since it's not directly exposed by Persistence
                    '_contentId' => $contentInfo->id,
                    'value' => new FieldValue(
                        array(
                            'data' => $field->value->data,
                            'sortKey' => array( 'sort_key_string' => $field->value->sortKey )
                        )
                    )
                ) + (array)$field
            );
            $languageCodes[] = $field->languageCode;
        }
        foreach ( array_unique( $languageCodes ) as $languageCode )
        {
            $languageIds[] = $this->handler->contentLanguageHandler()
                ->loadByLanguageCode( $languageCode )->id;
        }
        /** @var \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo */
        $versionInfo = $this->backend->create(
            'Content\\VersionInfo',
            array(
                // @todo: Name should be computed!
                'names' => $content->name,
                'creatorId' => $content->ownerId,
                'creationDate' => $content->modified,
                'modificationDate' => $content->modified,
                '_contentId' => $contentInfo->id,
                'status' => VersionInfo::STATUS_DRAFT,
                'versionNo' => 1,
                'languageIds' => $languageIds,
                'initialLanguageCode' => $this->handler->contentLanguageHandler()
                    ->load( $content->initialLanguageId )->languageCode
            )
        );
        $versionInfo->contentInfo = $contentInfo;
        $contentObj->versionInfo = $versionInfo;

        $locationHandler = $this->handler->locationHandler();
        foreach ( $content->locations as $locationStruct )
        {
            $locationStruct->contentId = $contentInfo->id;
            $locationStruct->contentVersion = $contentInfo->currentVersionNo;
            $contentObj->locations[] = $locationHandler->create( $locationStruct );
        }
        return $contentObj;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function createDraftFromVersion( $contentId, $srcVersion, $userId )
    {
        $content = $this->load( $contentId, $srcVersion );
        $contentInfo = $content->versionInfo->contentInfo;
        $fields = $content->fields;
        /** @var \eZ\Publish\SPI\Persistence\Content\VersionInfo $aVersion */
        $aVersion = $this->backend->find(
            'Content\\VersionInfo',
            array(
                '_contentId' => $contentId,
                'versionNo' => $srcVersion
            )
        );
        if ( empty( $aVersion ) )
            throw new NotFound( "Version", "contentId: $contentId // versionNo: $srcVersion" );

        // Create new version
        $newVersionNo = $this->getLastVersionNumber( $contentId ) + 1;
        $time = time();
        $content->versionInfo = $this->backend->create(
            'Content\\VersionInfo',
            array(
                'modificationDate' => $time,
                'creatorId' => $userId,
                'creationDate' => $time,
                '_contentId' => $contentId,
                'status' => VersionInfo::STATUS_DRAFT,
                'versionNo' => $newVersionNo,
                'initialLanguageCode' => $content->versionInfo->initialLanguageCode,
                'languageIds' => $content->versionInfo->languageIds,
                "names" => $content->versionInfo->names
            )
        );

        // Duplicate fields
        $content->fields = array();
        foreach ( $fields as $field )
        {
            $content->fields[] = $this->backend->create(
                'Content\\Field',
                array( 'versionNo' => $newVersionNo, '_contentId' => $contentId ) + (array)$field
            );
        }

        // set content info back on new version info
        $content->versionInfo->contentInfo = $contentInfo;

        return $content;
    }

    /**
     * Copy Content with Fields and Versions from $contentId in $version.
     *
     * Copies all fields from $contentId in $version (or all versions if false)
     * to a new object which is returned. Version numbers are maintained.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If content or version is not found
     *
     * @param mixed $contentId
     * @param mixed|null $versionNo Copy all versions if left null
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     * @todo Language support
     */
    public function copy( $contentId, $versionNo = null )
    {
        $originalContentInfo = $this->backend->load( 'Content\\ContentInfo', $contentId );
        if ( !$originalContentInfo )
            throw new NotFound( 'Content\\ContentInfo', "contentId: $contentId" );

        $time = time();

        $currentVersionNo = isset( $versionNo ) ? $versionNo : $originalContentInfo->currentVersionNo;
        $contentObj = $this->backend->create( 'Content', array( '_currentVersionNo' => $currentVersionNo ) );
        $contentInfo = $this->backend->create(
            'Content\\ContentInfo',
            array(
                "contentTypeId" => $originalContentInfo->contentTypeId,
                "sectionId" => $originalContentInfo->sectionId,
                "ownerId" => $originalContentInfo->ownerId,
                "isPublished" => false,
                "currentVersionNo" => $currentVersionNo,
                "mainLanguageCode" => $originalContentInfo->mainLanguageCode,
                "modificationDate" => 0,
                "publicationDate" => 0,
                "alwaysAvailable" => $originalContentInfo->alwaysAvailable
            )
        );

        // Copy version(s)
        foreach (
            $this->backend->find(
                "Content\\VersionInfo",
                isset( $versionNo ) ?
                    array( "_contentId" => $originalContentInfo->id, "versionNo" => $versionNo ) :
                    array( "_contentId" => $originalContentInfo->id )
            ) as $versionInfo )
        {
            $this->backend->create(
                "Content\\VersionInfo",
                array(
                    "names" => $versionInfo->names,
                    "versionNo" => $versionInfo->versionNo,
                    "modificationDate" => $time,
                    "creatorId" => $versionInfo->creatorId,
                    "creationDate" => $time,
                    "_contentId" => $contentInfo->id,
                    "initialLanguageCode" => $versionInfo->initialLanguageCode,
                    "languageIds" => $versionInfo->languageIds,
                    "status" => $versionInfo->versionNo === $currentVersionNo ?
                        VersionInfo::STATUS_DRAFT :
                        $versionInfo->status
                )
            );
        }

        // Associate last version to content VO
        $aVersion = $this->backend->find(
            'Content\\VersionInfo',
            array(
                '_contentId' => $contentInfo->id,
                'versionNo' => $currentVersionNo
            )
        );
        if ( empty( $aVersion ) )
            throw new NotFound( "Version", "contentId: {$contentInfo->id} // versionNo: $currentVersionNo" );

        $contentObj->versionInfo = $aVersion[0];
        $contentObj->versionInfo->contentInfo = $contentInfo;

        // Copy fields
        // @todo: language support
        foreach (
            $this->backend->find(
                "Content\\Field",
                // Using internal _contentId since it's not directly exposed by Persistence
                isset( $versionNo ) ?
                    array( "_contentId" => $originalContentInfo->id, "versionNo" => $versionNo ) :
                    array( "_contentId" => $originalContentInfo->id )
            ) as $field
        )
        {
            $this->backend->create(
                'Content\\Field',
                array( '_contentId' => $contentInfo->id ) + (array)$field
            );
        }

        // Associate last version's fields
        // @todo: Throw NotFound if no fields at all ?
        $contentObj->fields = $this->backend->find(
            "Content\\Field",
            array(
                "_contentId" => $contentInfo->id,
                "versionNo" => $currentVersionNo
            )
        );

        return $contentObj;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function load( $id, $version, $translations = null )
    {
        $res = $this->backend->find(
            'Content',
            array( 'id' => $id ),
            array(
                'locations' => array(
                    'type' => 'Content\\Location',
                    'match' => array( 'contentId' => 'id' )
                )
            )
        );
        if ( empty( $res ) )
            throw new NotFound( "Content", "contentId:{$id}" );

        $content = $res[0];
        $versions = $this->backend->find(
            'Content\\VersionInfo',
            array(
                '_contentId' => $id,
                'versionNo' => $version
            ),
            array(
                'contentInfo' => array(
                    'type' => 'Content\\ContentInfo',
                    'match' => array( 'id' => '_contentId' ),
                    'single' => true
                )
            )
        );
        if ( !isset( $versions[0] ) )
            throw new NotFound( "Version", "contentId:{$id}, versionNo:{$version}" );

        if ( !$versions[0]->contentInfo instanceof ContentInfo )
            throw new NotFound( "Content\\ContentInfo", "contentId:{$id}" );

        $fieldMatch = array(
            "_contentId" => $id,
            "versionNo" => $version
        );
        if ( isset( $translations ) )
            $fieldMatch["languageCode"] = $translations;
        $content->fields = $this->backend->find( 'Content\\Field', $fieldMatch );

        $content->versionInfo = $versions[0];

        $locations = $this->backend->find(
            'Content\\Location',
            array( 'contentId' => $content->versionInfo->contentInfo->id )
        );
        if ( !empty( $locations ) )
        {
            $content->versionInfo->contentInfo->mainLocationId = $locations[0]->mainLocationId;
        }

        return $content;
    }

    /**
     * Returns the metadata object for a content identified by $contentId.
     *
     * @param int|string $contentId
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    public function loadContentInfo( $contentId )
    {
        $contentInfo = $this->backend->load( 'Content\\ContentInfo', $contentId );
        $locations = $this->backend->find(
            'Content\\Location',
            array( 'contentId' => $contentInfo->id )
        );
        if ( !empty( $locations ) )
        {
            $contentInfo->mainLocationId = $locations[0]->mainLocationId;
        }

        return $contentInfo;
    }

    /**
     * Returns the version object for a content/version identified by $contentId and $versionNo
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If version is not found
     *
     * @param int|string $contentId
     * @param int $versionNo Version number to load
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    public function loadVersionInfo( $contentId, $versionNo )
    {
        $versionInfoList = $this->backend->find(
            'Content\\VersionInfo',
            array(
                '_contentId' => $contentId,
                'versionNo' => $versionNo
            ),
            array(
                'contentInfo' => array(
                    'type' => 'Content\\ContentInfo',
                    'match' => array( 'id' => '_contentId' ),
                    'single' => true
                )
            )
        );

        if ( empty( $versionInfoList ) )
            throw new NotFound( "Content\\VersionInfo", "contentId: $contentId, versionNo: $versionNo" );

        $versionInfo = reset( $versionInfoList );
        $locations = $this->backend->find(
            'Content\\Location',
            array( 'contentId' => $contentId )
        );
        if ( !empty( $locations ) )
        {
            $versionInfo->contentInfo->mainLocationId = $locations[0]->mainLocationId;
        }

        return $versionInfo;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function loadDraftsForUser( $userId )
    {
        $versionInfoList = $this->backend->find(
            'Content\\VersionInfo',
            array(
                "status" => VersionInfo::STATUS_DRAFT,
                "creatorId" => $userId
            ),
            array(
                'contentInfo' => array(
                    'type' => 'Content\\ContentInfo',
                    'match' => array( 'id' => '_contentId' ),
                    'single' => true
                )
            )
        );

        foreach ( $versionInfoList as $versionInfo )
        {
            $locations = $this->backend->find(
                'Content\\Location',
                array( 'contentId' => $versionInfo->contentInfo->id )
            );
            if ( !empty( $locations ) )
            {
                $versionInfo->contentInfo->mainLocationId = $locations[0]->mainLocationId;
            }
        }

        return $versionInfoList;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function setStatus( $contentId, $status, $version )
    {
        $versions = $this->backend->find( 'Content\\VersionInfo', array( '_contentId' => $contentId, 'versionNo' => $version ) );

        if ( !count( $versions ) )
        {
            throw new NotFound( "Version", "contentId: $contentId, versionNo: $version" );
        }
        return $this->backend->update( 'Content\\VersionInfo', $versions[0]->id, array( 'status' => $status ) );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function setObjectState( $contentId, $stateGroup, $state )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function getObjectState( $contentId, $stateGroup )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function updateMetadata( $contentId, MetadataUpdateStruct $content )
    {
        $updateData = (array) $content;
        $updateData["alwaysAvailable"] = $updateData["alwaysAvailable"];
        $updateData["mainLanguageCode"] = $this->handler->contentLanguageHandler()
            ->load( $content->mainLanguageId )->languageCode;

        $this->backend->update(
            "Content\\ContentInfo",
            $contentId,
            $updateData,
            true
        );

        return $this->loadContentInfo( $contentId );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function updateContent( $contentId, $versionNo, UpdateStruct $content )
    {
        $versionNames = $this->loadVersionInfo( $contentId, $versionNo )->names;
        foreach ( $versionNames as $languageCode => &$versionName )
            if ( array_key_exists( $languageCode, $content->name ) ) $versionName = $content->name[$languageCode];
        $versionUpdateData = array(
            "creatorId" => $content->creatorId,
            "modificationDate" => $content->modificationDate,
            "names" => $versionNames,
            "initialLanguageCode" => $this->handler->contentLanguageHandler()
                ->load( $content->initialLanguageId )->languageCode
        );

        $this->backend->updateByMatch(
            "Content\\VersionInfo",
            array( "_contentId" => $contentId, "versionNo" => $versionNo ),
            $versionUpdateData
        );

        foreach ( $content->fields as $field )
        {
            $fieldType = $this->backend->load( "Content\\Type\\FieldDefinition", $field->fieldDefinitionId );
            if ( $fieldType->isTranslatable )
                $this->backend->updateByMatch(
                    "Content\\Field",
                    array(
                        "id" => $field->id,
                        "versionNo" => $versionNo
                    ),
                    array( "value" => $field->value )
                );
            else
                $this->backend->updateByMatch(
                    "Content\\Field",
                    array(
                        "fieldDefinitionId" => $field->fieldDefinitionId,
                        "versionNo" => $versionNo,
                        "_contentId" => $contentId
                    ),
                    array( "value" => $field->value )
                );
        }

        return $this->load( $contentId, $versionNo );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     * @todo add deleting of relations
     */
    public function deleteContent( $contentId )
    {
        $this->backend->delete( 'Content', $contentId );
        $this->backend->delete( 'Content\\ContentInfo', $contentId );

        $versions = $this->backend->find( 'Content\\VersionInfo', array( '_contentId' => $contentId ) );
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

            $this->backend->delete( 'Content\\VersionInfo', $version->id );
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
     * Deletes given version, its fields, node assignment, relations and names.
     *
     * Removes the relations, but not the related objects.
     *
     * @param int $contentId
     * @param int $versionNo
     *
     * @return boolean
     *
     * @todo add deleting of relations
     */
    public function deleteVersion( $contentId, $versionNo )
    {
        $versions = $this->backend->find(
            'Content\\VersionInfo',
            array(
                "_contentId" => $contentId,
                "versionNo" => $versionNo
            )
        );
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

            $this->backend->delete( 'Content\\VersionInfo', $version->id );
        }
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function trash( $contentId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function untrash( $contentId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no version found
     */
    public function listVersions( $contentId )
    {
        $versions = $this->backend->find(
            'Content\\VersionInfo',
            array(
                '_contentId' => $contentId
            ),
            array(
                'contentInfo' => array(
                    'type' => 'Content\\ContentInfo',
                    'match' => array( 'id' => '_contentId' ),
                    'single' => true
                )
            )
        );

        if ( empty( $versions ) )
            throw new NotFound( "Content\\VersionInfo", "contentId: $contentId" );

        return $versions;
    }

    /**
     * Creates a relation between $sourceContentId in $sourceContentVersionNo
     * and $destinationContentId with a specific $type.
     *
     * @param  \eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct $relation
     * @return \eZ\Publish\SPI\Persistence\Content\Relation
     */
    public function addRelation( RelationCreateStruct $relation )
    {
        // Ensure source content exists
        $sourceContent = $this->backend->find( 'Content\\ContentInfo', array( "id" => $relation->sourceContentId ) );

        if ( empty( $sourceContent ) )
            throw new NotFound( 'Content\\ContentInfo', "contentId: {$relation->sourceContentId}" );

        // Ensure source content exists if version is specified
        if ( $relation->sourceContentVersionNo !== null )
        {
            $version = $this->backend->find( "Content\\VersionInfo", array( "_contentId" => $relation->sourceContentId, "versionNo" => $relation->sourceContentVersionNo ) );

            if ( empty( $version ) )
                throw new NotFound( "Content\\VersionInfo", "contentId: {$relation->sourceContentId}, versionNo: {$relation->sourceContentVersionNo}" );
        }

        // Ensure destination content exists
        $destinationContent = $this->backend->find( 'Content\\ContentInfo', array( "id" => $relation->destinationContentId ) );

        if ( empty( $destinationContent ) )
            throw new NotFound( 'Content\\ContentInfo', "contentId: {$relation->destinationContentId}" );

        return $this->backend->create( "Content\\Relation", (array)$relation );
    }

    /**
     * Removes a relation by relation Id.
     *
     * @param mixed $relationId
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if relation to be removed is not found.
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
     * Loads relations from $sourceContentId. Optionally, loads only those with $type and $sourceContentVersionNo.
     *
     * @param mixed $sourceContentId Source Content ID
     * @param mixed|null $sourceContentVersionNo Source Content Version, null if not specified
     * @param int|null $type {@see \eZ\Publish\API\Repository\Values\Content\Relation::COMMON,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::EMBED,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::LINK,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::FIELD}
     * @return \eZ\Publish\SPI\Persistence\Content\Relation[]
     */
    public function loadRelations( $sourceContentId, $sourceContentVersionNo = null, $type = null )
    {
        $filter = array( "sourceContentId" => $sourceContentId );
        if ( $sourceContentVersionNo !== null )
            $filter["sourceContentVersionNo"] = $sourceContentVersionNo;

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
     * @param int|null $type {@see \eZ\Publish\API\Repository\Values\Content\Relation::COMMON,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::EMBED,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::LINK,
     *                 \eZ\Publish\API\Repository\Values\Content\Relation::FIELD}
     * @return \eZ\Publish\SPI\Persistence\Content\Relation[]
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
     * @param int $contentId
     * @param int $versionNo
     * @param \eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct $metaDataUpdateStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content The published Content
     */
    public function publish( $contentId, $versionNo, MetadataUpdateStruct $metaDataUpdateStruct )
    {
        // Change Content currentVersionNo to the published version
        $this->backend->update(
            'Content',
            $contentId,
            array(
                '_currentVersionNo' => $versionNo,
            )
        );

        // Update ContentInfo published flag and change the currentVersionNo to the published version
        $contentInfoUpdateData = array(
            "currentVersionNo" => $versionNo,
            "isPublished" => true,
        );
        // Update ContentInfo with set properties in $metaDataUpdateStruct
        foreach ( $metaDataUpdateStruct as $propertyName => $propertyValue )
        {
            if ( isset( $propertyValue ) )
            {
                if ( $propertyName === "alwaysAvailable" )
                {
                    $contentInfoUpdateData["alwaysAvailable"] = $propertyValue;
                }
                elseif ( $propertyName === "mainLanguageId" )
                {
                    $contentInfoUpdateData["mainLanguageCode"] =
                        $this->handler->contentLanguageHandler()->load( $propertyValue )->languageCode;
                }
                else $contentInfoUpdateData[$propertyName] = $propertyValue;
            }
        }
        $this->backend->update(
            'Content\\ContentInfo',
            $contentId,
            $contentInfoUpdateData,
            true
        );

        // Update VersionInfo with modified timestamp and published status
        $this->backend->updateByMatch(
            'Content\\VersionInfo',
            array( '_contentId' => $contentId, 'versionNo' => $versionNo ),
            array(
                "modificationDate" => $metaDataUpdateStruct->modificationDate,
                "status" => VersionInfo::STATUS_PUBLISHED,
            )
        );

        return $this->load( $contentId, $versionNo );
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
            'Content\\VersionInfo',
            array(
                '_contentId' => $contentId
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
