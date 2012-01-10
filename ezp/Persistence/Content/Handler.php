<?php
/**
 * File containing the Content Handler interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\UpdateStruct,
    // @todo We must verify whether we want to type cast on the "Criterion" interface or abstract class
    ezp\Persistence\Content\Query\Criterion as AbstractCriterion,
    ezp\Persistence\Content\RestrictedVersion,
    ezp\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * The Content Handler interface defines content operations on the storage engine.
 *
 * The basic operations which are performed on content objects are collected in
 * this interface. Typically this interface would be used by a service managing
 * business logic for content objects.
 */
interface Handler
{
    /**
     * Creates a new Content entity in the storage engine.
     *
     * The values contained inside the $content will form the basis of stored
     * entity.
     *
     * Will contain always a complete list of fields.
     *
     * @param \ezp\Persistence\Content\CreateStruct $content Content creation struct.
     * @return \ezp\Persistence\Content Content value object
     */
    public function create( CreateStruct $content );

    /**
     * Creates a new draft version from $contentId in $srcVersion number.
     *
     * Copies all fields from $contentId in $srcVersion and creates a new
     * version of the referred Content from it.
     *
     * @param mixed $contentId
     * @param int $srcVersion
     * @return \ezp\Persistence\Content\Version
     * @throws \ezp\Base\Exception\NotFound Thrown if $contentId and/or $srcVersion are invalid
     */
    public function createDraftFromVersion( $contentId, $srcVersion );

    /**
     * Returns the raw data of a content object identified by $id, in a struct.
     *
     * A version to load must be specified. If you want to load the current
     * version of a content object use SearchHandler::findSingle() with the
     * ContentId criterion.
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @param int|string $id
     * @param int|string $version
     * @param string[] $translations
     * @return \ezp\Persistence\Content Content value object
     */
    public function load( $id, $version, $translations = null );

    /**
     * Sets the state of object identified by $contentId and $version to $status.
     *
     * The $status can be one of STATUS_DRAFT, STATUS_PUBLISHED, STATUS_ARCHIVED
     * @todo Is this supposed to be constants from Content or Version? They differ..
     *
     * @param mixed $contentId
     * @param int $status
     * @param int $version
     * @see ezp\Content
     * @return boolean
     */
    public function setStatus( $contentId, $status, $version );

    /**
     * Sets the object-state of object identified by $contentId and $stateGroup to $state.
     *
     * The $state is the id of the state within one group.
     *
     * @param mixed $contentId
     * @param mixed $stateGroup
     * @param mixed $state
     * @return boolean
     * @see ezp\Content
     */
    public function setObjectState( $contentId, $stateGroup, $state );

    /**
     * Gets the object-state of object identified by $contentId and $stateGroup to $state.
     *
     * The $state is the id of the state within one group.
     *
     * @param mixed $contentId
     * @param mixed $stateGroup
     * @return mixed
     * @see ezp\Content
     */
    public function getObjectState( $contentId, $stateGroup );

    /**
     * Updates a content object entity with data and identifier $content
     *
     * @param \ezp\Persistence\Content\UpdateStruct $content
     * @return \ezp\Persistence\Content
     */
    public function update( UpdateStruct $content );

    /**
     * Deletes all versions and fields, all locations (subtree), and all relations.
     *
     * Removes the relations, but not the related objects. All subtrees of the
     * assigned nodes of this content objects are removed (recursively).
     *
     * @param int $contentId
     * @return boolean
     */
    public function delete( $contentId );

    /**
     * Return the versions for $contentId
     *
     * @param int $contentId
     * @return \ezp\Persistence\Content\RestrictedVersion[]
     */
    public function listVersions( $contentId );

    /**
     * Copy Content with Fields and Versions from $contentId in $version.
     *
     * Copies all fields from $contentId in $version (or all versions if false)
     * to a new object which is returned. Version numbers are maintained.
     *
     * @param mixed $contentId
     * @param int|false $version Copy all versions if left false
     * @return \ezp\Persistence\Content
     * @throws \ezp\Base\Exception\NotFound If content or version is not found
     */
    public function copy( $contentId, $version );

    /**
     * Creates a relation between $sourceContentId in $sourceContentVersionNo
     * and $destinationContentId with a specific $type.
     *
     * @todo Should the existence verifications happen here or is this supposed to be handled at a higher level?
     *
     * @param  \ezp\Persistence\Content\Relation\CreateStruct $relation
     * @return \ezp\Persistence\Content\Relation
     */
    public function addRelation( RelationCreateStruct $relation );

    /**
     * Removes a relation by relation Id.
     *
     * @todo Should the existence verifications happen here or is this supposed to be handled at a higher level?
     *
     * @param mixed $relationId
     */
    public function removeRelation( $relationId );

    /**
     * Loads relations from $sourceContentId. Optionally, loads only those with $type and $sourceContentVersionNo.
     *
     * @param mixed $sourceContentId Source Content ID
     * @param mixed|null $sourceContentVersionNo Source Content Version, null if not specified
     * @param int|null $type {@see \ezp\Content\Relation::COMMON, \ezp\Content\Relation::EMBED, \ezp\Content\Relation::LINK, \ezp\Content\Relation::ATTRIBUTE}
     * @return \ezp\Persistence\Content\Relation[]
     */
    public function loadRelations( $sourceContentId, $sourceContentVersionNo = null, $type = null );

    /**
     * Loads relations from $contentId. Optionally, loads only those with $type.
     *
     * Only loads relations against published versions.
     *
     * @param mixed $destinationContentId Destination Content ID
     * @param int|null $type {@see \ezp\Content\Relation::COMMON, \ezp\Content\Relation::EMBED, \ezp\Content\Relation::LINK, \ezp\Content\Relation::ATTRIBUTE}
     * @return \ezp\Persistence\Content\Relation[]
     */
    public function loadReverseRelations( $destinationContentId, $type = null );

    /**
     * Performs the publishing operations required to set the version identified by $updateStruct->versionNo and
     * $updateStruct->id as the published one.
     *
     * The UpdateStruct will also contain an array of Content name indexed by Locale.
     *
     * @param \ezp\Persistence\Content\UpdateStruct An UpdateStruct with id, versionNo and name array
     *
     * @return \ezp\Persistence\Content The published Content
     */
    public function publish( UpdateStruct $updateStruct );
}
