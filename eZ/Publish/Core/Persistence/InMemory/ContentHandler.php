<?php
/**
 * File containing the ContentHandler implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\Core\Persistence\InMemory;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandlerInterface,
    eZ\Publish\SPI\Persistence\Content\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct,
    eZ\Publish\SPI\Persistence\Content\RestrictedVersion,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content,
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
     * @see eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function create( CreateStruct $content )
    {
        $contentObj = $this->backend->create( 'Content', array() );
        $contentObj->contentInfo = $this->backend->create(
            'Content\\ContentInfo',
            array(
                'contentTypeId' => $content->typeId,
                'sectionId' => $content->sectionId,
                'ownerId' => $content->ownerId,
                'status' => VersionInfo::STATUS_DRAFT,
                'currentVersionNo' => 1,
                'modificationDate' => $content->modified,
                'publicationDate' => $content->published,
            ),
            true,
            'contentId'
        );
        $time = time();
        $versionInfo = $this->backend->create(
            'Content\\VersionInfo',
            array(
                // @todo: Name should be computed!
                'names' => $content->name,
                'modificationDate' => $time,
                'creatorId' => $content->ownerId,
                'creationDate' => $time,
                'contentId' => $contentObj->contentInfo->contentId,
                'status' => VersionInfo::STATUS_DRAFT,
                'versionNo' => 1
            )
        );
        foreach ( $content->fields as $field )
        {
            $contentObj->fields[] = $this->backend->create(
                'Content\\Field',
                array(
                    'versionNo' => $versionInfo->versionNo,
                    // Using internal _contentId since it's not directly exposed by Persistence
                    '_contentId' => $contentObj->contentInfo->contentId,
                    'value' => new FieldValue(
                        array(
                            'data' => $field->value->data,
                            'sortKey' => array( 'sort_key_string' => $field->value->sortKey )
                        )
                    )
                ) + (array)$field
            );
        }
        $contentObj->versionInfo = $versionInfo;

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
     * @see eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function createDraftFromVersion( $contentId, $srcVersion )
    {
        $content = $this->load( $contentId, $srcVersion );
        $fields = $content->fields;
        $aVersion = $this->backend->find(
            'Content\\VersionInfo',
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
        $content->versionInfo = $this->backend->create(
            'Content\\VersionInfo',
            array(
                'modificationDate' => $time,
                // @todo: implement real user
                'creatorId' => $aVersion[0]->creatorId,
                'creationDate' => $time,
                'contentId' => $contentId,
                'status' => VersionInfo::STATUS_DRAFT,
                'versionNo' => $newVersionNo
            )
        );

        // Duplicate fields
        // @todo: language support
        $content->fields = array();
        foreach ( $fields as $field )
        {
            $content->fields[] = $this->backend->create(
                'Content\\Field',
                array( 'versionNo' => $newVersionNo, '_contentId' => $contentId ) + (array)$field
            );
        }

        return $content;
    }

    /**
     * Copy Content with Fields and Versions from $contentId in $version.
     *
     * Copies all fields from $contentId in $version (or all versions if false)
     * to a new object which is returned. Version numbers are maintained.
     *
     * @param int $contentId
     * @param int|false $versionNo Copy all versions if left false
     * @return \eZ\Publish\SPI\Persistence\Content
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If content or version is not found
     * @todo Language support
     */
    public function copy( $contentId, $versionNo )
    {
        $contentInfo = $this->backend->load( 'Content\\ContentInfo', $contentId, 'contentId' );
        if ( !$contentInfo )
            throw new NotFound( 'Content\\ContentInfo', "contentId: $contentId" );

        $time = time();

        $currentVersionNo = $versionNo === false ? $contentInfo->currentVersionNo : $versionNo;
        $contentObj = $this->backend->create( 'Content', array() );
        $contentObj->contentInfo = $this->backend->create(
            'Content\\ContentInfo', array(
                "contentTypeId" => $contentInfo->contentTypeId,
                "sectionId" => $contentInfo->sectionId,
                "ownerId" => $contentInfo->ownerId,
                "isPublished" => $contentInfo->isPublished,
                "currentVersionNo" => $currentVersionNo,
                "mainLanguageCode" => $contentInfo->mainLanguageCode,
                "modificationDate" => $time,
                "publicationDate" => $time
            ),
            true,
            'contentId'
        );

        // Copy version(s)
        foreach (
            $this->backend->find(
                "Content\\VersionInfo",
                $versionNo === false ?
                array( "contentId" => $contentInfo->contentId ) :
                array( "contentId" => $contentInfo->contentId, "versionNo" => $versionNo )
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
                    "contentId" => $contentObj->contentInfo->contentId,
                    "status" => $versionInfo->status,
                    "initialLanguageCode" => $versionInfo->initialLanguageCode,
                    "languageIds" => $versionInfo->languageIds
                )
            );
        }

        // Associate last version to content VO
        $aVersion = $this->backend->find(
            'Content\\VersionInfo',
            array(
                'contentId' => $contentObj->contentInfo->contentId,
                'versionNo' => $currentVersionNo
            )
        );
        if ( empty( $aVersion ) )
            throw new NotFound( "Version", "contentId: $contentObj->contentInfo->contentId // versionNo: $currentVersionNo" );

        $contentObj->versionInfo = $aVersion[0];

        // Copy fields
        // @todo: language support
        foreach (
            $this->backend->find(
                "Content\\Field",
                // Using internal _contentId since it's not directly exposed by Persistence
                $versionNo === false ?
                array( "_contentId" => $contentInfo->contentId ) :
                array( "_contentId" => $contentInfo->contentId, "versionNo" => $versionNo )
            ) as $field
        )
        {
            $this->backend->create(
                'Content\\Field',
                array( '_contentId' => $contentObj->contentInfo->contentId ) + (array)$field
            );
        }

        // Associate last version's fields
        // @todo: Throw NotFound if no fields at all ?
        $contentObj->fields = $this->backend->find(
            "Content\\Field",
            array(
                "_contentId" => $contentObj->contentInfo->contentId,
                "versionNo" => $currentVersionNo
            )
        );

        return $contentObj;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Handler
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
                ),
                'contentInfo' => array(
                    'type' => 'Content\\ContentInfo',
                    'match' => array( 'contentId' => 'id' ),
                    'single' => true
                )
            )
        );
        if ( empty( $res ) )
            throw new NotFound( "Content", "contentId:{$id}" );

        $content = $res[0];
        if ( !$content->contentInfo instanceof ContentInfo )
            throw new NotFound( "Content\\ContentInfo", "contentId:{$id}" );

        $versions = $this->backend->find( 'Content\\VersionInfo', array( 'contentId' => $content->contentInfo->contentId, 'versionNo' => $version ) );
        if ( !isset( $versions[0] ) )
            throw new NotFound( "Version", "contentId:{$id}, versionNo:{$version}" );

        $content->fields = $this->backend->find(
            'Content\\Field',
            array(
                "_contentId" => $content->contentInfo->contentId,
                "versionNo" => $version
            )
        );

        $content->versionInfo = $versions[0];
        $content->locations = $this->backend->find( 'Content\\Location', array( 'contentId' => $content->contentInfo->contentId  ) );
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
        return $this->backend->load( 'Content\\ContentInfo', $contentId, 'contentId' );
    }

    /**
     * Returns the version object for a content/version identified by $contentId and $versionNo
     *
     * @param int|string $contentId
     * @param int $versionNo Version number to load
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    public function loadVersionInfo( $contentId, $versionNo )
    {
        $versionInfo = $this->backend->find(
            'Content\\VersionInfo',
            array(
                'contentId' => $contentId,
                'versionNo' => $versionNo
            )
        );

        return reset( $versionInfo );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function loadDraftsForUser( $userId )
    {
        return $this->backend->find(
            "Content\\VersionInfo",
            array(
                "status"    => VersionInfo::STATUS_DRAFT,
                "creatorId" => $userId
            )
        );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function setStatus( $contentId, $status, $version )
    {
        $versions = $this->backend->find( 'Content\\VersionInfo', array( 'contentId' => $contentId, 'versionNo' => $version ) );

        if ( !count( $versions ) )
        {
            throw new NotFound( "Version", "contentId: $contentId, versionNo: $version" );
        }
        return $this->backend->update( 'Content\\VersionInfo', $versions[0]->id, array( 'status' => $status ) );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function setObjectState( $contentId, $stateGroup, $state )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function getObjectState( $contentId, $stateGroup )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function update( UpdateStruct $content )
    {
        // @todo Assume the attached version to Content is the one that should be updated
        $this->backend->update(
            'Content\\ContentInfo',
            $content->id,
            array(
                "ownerId" => $content->ownerId,
            )
        );

        $this->backend->updateByMatch(
            'Content\\VersionInfo',
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
     * Updates a content object meta data, identified by $contentId
     *
     * @param int $contentId
     * @param \eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct $content
     * @return \eZ\Publish\SPI\Persistence\ContentInfo
     */
    public function updateMetadata( $contentId, MetadataUpdateStruct $content )
    {
        throw new \Exception( 'Not implemented yet' );
    }

    /**
     * Updates a content version, identified by $contentId and $versionNo
     *
     * @param int $contentId
     * @param int $versionNo
     * @param \eZ\Publish\SPI\Persistence\Content\UpdateStruct $content
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function updateContent( $contentId, $versionNo, UpdateStruct $content )
    {
        throw new \Exception( 'Not implemented yet' );
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler
     * @todo add deleting of relations
     */
    public function delete( $contentId, $versionNo = null )
    {
        $versionFindMatch = array( 'contentId' => $contentId );
        if ( isset( $versionNo ) )
        {
            $versionFindMatch["versionNo"] = $versionNo;
        }
        else
        {
            $this->backend->delete( 'Content\\ContentInfo', $contentId, 'contentId' );

            // @todo Deleting Locations by content object id should be possible using handler API?
            $locationHandler = $this->handler->locationHandler();
            $locations = $this->backend->find( 'Content\\Location', array( 'contentId' => $contentId ) );
            foreach ( $locations as $location )
            {
                $locationHandler->removeSubtree( $location->id );
            }
        }

        $versions = $this->backend->find( 'Content\\VersionInfo', $versionFindMatch );
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
     * @see eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function trash( $contentId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function untrash( $contentId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see eZ\Publish\SPI\Persistence\Content\Handler
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If no version found
     */
    public function listVersions( $contentId )
    {
        $versions = $this->backend->find( "Content\\VersionInfo", array( "contentId" => $contentId ) );

        if ( empty( $versions ) )
            throw new NotFound( "Content\\VersionInfo", "contentId: $contentId" );

        // cast to RestrictedVersion
        foreach ( $versions as $key => $vo )
        {
            $restricted = new RestrictedVersion();
            foreach ( $restricted as $property => $value )
            {
                switch ( $property )
                {
                    case 'name':
                        $restricted->name = $vo->names;
                        break;

                    case 'modified':
                        $restricted->modified = $vo->modificationDate;
                        break;

                    case 'created':
                        $restricted->created = $vo->creationDate;
                        break;

                    case 'initialLanguageId':
                        $languages = $this->backend->find( 'Content\\Language', array( 'languageCode' => $vo->initialLanguageCode ) );
                        $restricted->initialLanguageId = $languages[0]->id;
                        break;

                    default:
                        $restricted->$property = $vo->$property;
                }
            }
            $versions[$key] = $restricted;
        }

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
        $sourceContent = $this->backend->find( 'Content\\ContentInfo', array( "contentId" => $relation->sourceContentId ) );

        if ( empty( $sourceContent ) )
            throw new NotFound( 'Content\\ContentInfo', "contentId: {$relation->sourceContentId}" );

        // Ensure source content exists if version is specified
        if ( $relation->sourceContentVersionNo !== null )
        {
            $version = $this->backend->find( "Content\\VersionInfo", array( "contentId" => $relation->sourceContentId, "versionNo" => $relation->sourceContentVersionNo ) );

            if ( empty( $version ) )
                throw new NotFound( "Content\\VersionInfo", "contentId: {$relation->sourceContentId}, versionNo: {$relation->sourceContentVersionNo}" );
        }

        // Ensure destination content exists
        $destinationContent = $this->backend->find( 'Content\\ContentInfo', array( "contentId" => $relation->destinationContentId ) );

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
     * @param int|null $type {@see \ezp\Content\Relation::COMMON, \ezp\Content\Relation::EMBED, \ezp\Content\Relation::LINK, \ezp\Content\Relation::ATTRIBUTE}
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
     * @param int|null $type {@see \ezp\Content\Relation::COMMON, \ezp\Content\Relation::EMBED, \ezp\Content\Relation::LINK, \ezp\Content\Relation::ATTRIBUTE}
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
     * @param \eZ\Publish\SPI\Persistence\Content\UpdateStruct An UpdateStruct with id and versionNo
     *
     * @return \eZ\Publish\SPI\Persistence\Content The published Content
     */
    public function publish( UpdateStruct $updateStruct )
    {
        // Change the currentVersionNo to the published version
        $this->backend->update(
            'Content\\ContentInfo', $updateStruct->id,
            array(
                'currentVersionNo' => $updateStruct->versionNo,
            )
        );

        // Change the currentVersionNo to the published version
        $this->backend->updateByMatch(
            'Content\\VersionInfo',
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
            'Content\\VersionInfo',
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
