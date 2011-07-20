<?php
/**
 * File containing the ContentHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Interfaces;
use ezp\Persistence\Content\ContentCreateStruct,
    ezp\Persistence\Content\ContentUpdateStruct,
    // @todo We must verify whether we want to type cast on the "Criterion" interface or abstract class
    ezp\Persistence\Content\Criterion as AbstractCriterion,
    ezp\Persistence\Content\RestrictedVersion;

/**
 * The ContentHandler interface defines content operations on the storage engine.
 *
 * The basic operations which are performed on content objects are collected in
 * this interface. Typically this interface would be used by a service managing
 * business logic for content objects.
 *
 * @version //autogentag//
 */
interface ContentHandler
{
    /**
     * Creates a new Content entity in the storage engine.
     *
     * The values contained inside the $content will form the basis of stored
     * entity.
     *
     * Will contain always a complete list of fields.
     *
     * @param ContentCreateStruct $content Content creation struct.
     * @return ezp\Persistence\Content Content value object
     */
    public function create( ContentCreateStruct $content );

    /**
     * Creates a new draft version from $contentId in $version.
     *
     * Copies all fields from $contentId in $srcVersion and creates a new
     * version of the referred Content from it.
     *
     * @param int $contentId
     * @param int|bool $srcVersion
     * @return ezp\Persistence\Content\Content
     */
    public function createDraftFromVersion( $contentId, $srcVersion );

    /**
     * Returns the raw data of a content object identified by $id, in a struct.
     *
     * @param int $id
     * @return ezp\Persistence\Content Content value object
     */
    public function load( $id );

    /**
     * Returns a list of object satisfying the $criteria.
     *
     * @param  ezp\Content\Criteria\Criteria $criteria
     * @param $offset
     * @param $limit
     * @param $sort
     * @return array(ezp\Persistence\Content) Content value object.
     */
    public function find( AbstractCriterion $criteria, $offset, $limit, $sort );

    /**
     * Returns a single Content object found.
     *
     * Performs a {@link find()} query to find a single object. You need to
     * ensure, that your $criteria ensure that only a single object can be
     * retrieved.
     *
     * @param ezp\Content\AbstractCriterion $criteria
     * @param mixed $offset
     * @param mixed $sort
     * @return ezp\Persistence\Content
     */
    public function findSingle( AbstractCriterion $criteria, $offset, $sort );

    /**
     * Sets the state of object identified by $contentId and $version to $state.
     *
     * The $state can be one of STATUS_DRAFT, STATUS_PUBLISHED, STATUS_ARCHIVED.
     *
     * @param int $contentId
     * @param int $state
     * @param int $version
     * @see ezp\Content\Content
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
     * @see ezp\Content\Content
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
     * @see ezp\Content\Content
     */
    public function getObjectState( $contentId, $stateGroup );

    /**
     * Updates a content object entity with data and identifier $content
     *
     * @param ContentUpdateStruct $content
     * @return ezp\Persistence\Content
     */
    public function update( ContentUpdateStruct $content );

    /**
     * Deletes all versions and fields, all locations (subtree), and all relations.
     *
     * Removes the relations, but not the related objects. Alle subtrees of the
     * assigned nodes of this content objects are removed (recursivley).
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

    /**
     * Fetch a content value object containing the values of the translation for $languageCode.
     *
     * This method might use field filters, if they are designed and available
     * at a later point in time.
     *
     * @param int $contentId
     * @param string $languageCode
     * @return ezp\Persistence\Content\Content
     */
    public function fetchTranslation( $contentId, $languageCode );
}
?>
