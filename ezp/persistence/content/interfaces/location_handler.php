<?php
/**
 * File containing the LocationHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage persistence_content
 */

namespace ezp\persistence\content;

/**
 * The LocationHandler interface defines operations on Location elements in the storage engine.
 *
 * @package ezp
 * @subpackage persistence_content
 */
interface LocationHandlerInterface extends \ezp\persistence\ServiceHandlerInterface
{

	/**
     * Returns the raw data for a location object, identified by $locationId, in a struct.
     *
	 * @param int $locationId
	 * @return \ezp\persistence\content\Location
	 */
	public function load( $locationId );

	/**
     * Copy location object identified by $sourceId, into destination location identified by $destinationId.
     *
	 * @param int $sourceId
	 * @param int $destinationId
     * @return boolean
     * @todo Decide whether a deep copy should have a dedicated method or have a $recursive param
	 */
	public function copy( $sourceId, $destinationId );

	/**
     * Moves location identified by $sourceId into new parent identified by $destinationId.
     *
	 * @param int $sourceId
	 * @param int $destinationId
     * @return boolean
	 */
	public function move( $sourceId, $destinationId );

	/**
     * Sets a location to be invisible.
     *
	 * @param int $id
	 */
	public function hide( $id );

	/**
     * Sets a location to be visible.
     *
	 * @param int $id
	 */
	public function unHide( $id );

	/**
     * Swaps the content object being pointed to by a location object.
     *
     * Make $locationId1 point to the content object in $locationId2, and vice
     * versa.
     *
	 * @param int $locationId1
	 * @param int $locationId2
     * @return boolean
	 */
	public function swap( $locationId1, $locationId2 );

	/**
     * Updates an existing location with data from $location.
     *
	 * @param \ezp\persistence\content\Location $location
     * @return boolean
	 */
	public function update( \ezp\persistence\content\Location $location );

    /**
     * Creates a new location for $contentId rooted at $parentId.
     *
     * @param int $contentId
     * @param int $parentId
     * @return \ezp\persistence\content\Location
     */
	public function createLocation( $contentId, $parentId );

    /**
     * Deletes a single location object, identified by $id.
     *
     * @param int $id
     * @return boolean
     */
    public function delete( $id );

	/**
     * Removes all content location under $locationId.
     *
	 * @param int $locationId
     * @return boolean
	 */
	public function removeSubtree( $locationId );

	/**
     * Create a (nice) url alias, $path pointing to $locationId, in $languageName.
     *
     * $alwaysAvailable controls whether the url alias is accessible in all languages.
     *
	 * @param string $path
	 * @param string $locationId
	 * @param string $languageName
	 * @param bool $alwaysAvailable
	 */
	public function storeUrlAliasPath( $path, $locationId, $languageName = null, $alwaysAvailable = false );

	/**
	 * @param string $languageCode
     * @todo Missing path or id?
	 */
	public function getPath( $languageCode );

	/**
	 * @param string $actionName
	 * @param array $actionValues
	 */
	public function getPathByActionList( $actionName, array $actionValues );
}
?>
