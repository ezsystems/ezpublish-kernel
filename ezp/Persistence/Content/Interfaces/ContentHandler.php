<?php
/**
 * File containing the ContentHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage persistence_content
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Interfaces;

/**
 * The ContentHandler interface defines content operations on the storage engine.
 *
 * The basic operations which are performed on content objects are collected in
 * this interface. Typically this interface would be used by a service managing
 * business logic for content objects.
 *
 * @package ezp
 * @subpackage persistence_content
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
     * @param values\ContentCreateStruct $content Content creation struct.
     * @return \ezp\Persistence\Content\Content Content value object
     */
    public function create(\ezp\Persistence\Content\ContentCreateStruct $content);

    /**
     * @param int $contentId
     * @param int|bool $srcVersion
     * @return \ezp\Persistence\Content\Content
     */
    public function createDraftFromVersion($contentId, $srcVersion = false);

    /**
     * Returns the raw data of a content object identified by $id, in a struct.
     *
     * @param int $id
     * @return \ezp\Persistence\Content\Content Content value object
     */
    public function load($id);

    /**
     * Returns one object satisfying the $criteria.
     *
     * @param Criteria $criteria
     * @param $limit
     * @param $sort
     * @return \ezp\Persistence\Content\Content Content value object.
     */
    public function find(\ezp\Content\Criteria\Criteria $criteria, $limit, $sort);

    /**
     * Returns an iterator containing all objects satisfying $criteria
     *
     * 
     * @param Criteria $criteria
     * @param $limit
     * @param $sort
     * @return mixed Collection of Content value objects
     */
    public function findIterator(\ezp\Content\Criteria\Criteria $criteria, $limit, $sort);

    /**
     * Sets the state of object identified by $contentId and $version to $state.
     *
     * The $state can be one of STATUS_DRAFT, STATUS_PUBLISHED, STATUS_ARCHIVED.
     *
     * @param int $contentId
     * @param int $state
     * @param int $version
     * @see \ezp\Content\Content
     * @return boolean
     */
    public function setState($contentId, $state, $version);

    /**
     * Sets the object-state of object identified by $contentId, $stateGroup and $version to $state.
     *
     * The $state is the id of the state within one group.
     *
     * @param int $contentId
     * @param int $stateGroup
     * @param int $state
     * @param int $version
     * @return boolean
     * @see \ezp\Content\Content
     */
    public function setObjectState($contentId, $stateGroup, $state, $version);

    /**
     * Updates a content object entity with data and identifier $content
     *
     * @param values\ContentUpdateStruct $content
     * @return boolean
     */
    public function update(\ezp\Persistence\Content\ContentUpdateStruct $content);

    /**
     * Deletes all versions and fields, all locations (subtree), and all relations.
     *
     * @param int $contentId
     * @return boolean
     */
    public function delete($contentId);

    /**
     * Sends a content object to trash.
     *
     * This is a suspended state, trashed objects retain all of their data and
     * knowledge of locations, but they are not returned in regular content
     * queries anymore.
     *
     * @param int $contentId
     * @return boolean
     */
    public function trash($contentId);

    /**
     * Returns a trashed object to normal state.
     *
     * The affected content object is now again part of matching content queries.
     *
     * @param int $contentId
     * @return boolean
     */
    public function untrash($contentId);

    /**
     * Return the versions for $contentId
     *
     * @param int $contentId
     * @return array
     */
    public function listVersions($contentId);

    /**
     * Fetch a content value object containing the values of the translation for $languageCode.
     *
     * @param int $contentId
     * @param string $languageCode
     * @return \ezp\Persistence\Content\Content
     */
    public function fetchTranslation($contentId, $languageCode);
}
?>
