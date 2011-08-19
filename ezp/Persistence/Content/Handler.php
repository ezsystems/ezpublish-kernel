<?php
/**
 * File containing the Content Handler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\UpdateStruct,
    // @todo We must verify whether we want to type cast on the "Criterion" interface or abstract class
    ezp\Persistence\Content\Criterion as AbstractCriterion,
    ezp\Persistence\Content\RestrictedVersion;

/**
 * The Content Handler interface defines content operations on the storage engine.
 *
 * The basic operations which are performed on content objects are collected in
 * this interface. Typically this interface would be used by a service managing
 * business logic for content objects.
 *
 * @version //autogentag//
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
     * Creates a new draft version from $contentId in $version.
     *
     * Copies all fields from $contentId in $srcVersion and creates a new
     * version of the referred Content from it.
     *
     * @param int $contentId
     * @param int|bool $srcVersion
     * @return \ezp\Persistence\Content\Content
     */
    public function createDraftFromVersion( $contentId, $srcVersion );

    /**
     * Copy Content with Fields and Versions from $contentId in $version.
     *
     * Copies all fields from $contentId in $version and creates a new
     * version of the referred Content from it.
     *
     * @param int $contentId
     * @param int|false $version
     * @return \ezp\Persistence\Content\Content
     * @todo Un comment when api is approved/voted/ok
     */
    //public function copy( $contentId, $version );

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
     * Sets the state of object identified by $contentId and $version to $state.
     *
     * The $state can be one of STATUS_DRAFT, STATUS_PUBLISHED, STATUS_ARCHIVED.
     *
     * @param int $contentId
     * @param int $state
     * @param int $version
     * @see ezp\Content
     * @return boolean
     */
    public function setState( $contentId, $state, $version );

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
     * @return array(RestrictedVersion)
     */
    public function listVersions( $contentId );
}
?>
