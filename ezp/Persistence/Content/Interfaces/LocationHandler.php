<?php
/**
 * File containing the LocationHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage persistence_content
 */

namespace ezp\Persistence\Content\Interfaces;

/**
 * The LocationHandler interface defines operations on Location elements in the storage engine.
 *
 * @package ezp
 * @subpackage persistence_content
 */
interface LocationHandler
{

    /**
     * Returns the raw data for a location object, identified by $locationId, in a struct.
     *
     * @param int $locationId
     * @return \ezp\Persistence\Content\Location
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
     * @param \ezp\Persistence\Content\Location $location
     * @return boolean
     */
    public function update( \ezp\Persistence\Content\Location $location );

    /**
     * Creates a new location for $contentId rooted at $parentId.
     *
     * @param int $contentId
     * @param int $parentId
     * @return \ezp\Persistence\Content\Location
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
     * $alwaysAvailable controls whether the url alias is accessible in all
     * languages.
     *
     * @param string $path
     * @param string $locationId
     * @param string $languageName
     * @param bool $alwaysAvailable
     */
    public function storeUrlAliasPath( $path, $locationId, $languageName = null, $alwaysAvailable = false );

    /**
     * Create a user chosen $alias pointing to $locationId in $languageName.
     *
     * If $languageName is null the $alias is created in the system's default
     * language. $alwaysAvailable makes the alias available in all languages.
     *
     * @param string $alias
     * @param int $locationId
     * @param boolean $forwarding
     * @param string $languageName
     * @param bool $alwaysAvailable
     * @return boolean
     */
    public function createCustomUrlAlias( $alias, $locationId, $forwarding = false, $languageName = null, $alwaysAvailable = false );

    /**
     * Create a history url entry.
     *
     * History url entries constitutes a log of earlier url aliases to a location,
     * and allows old urls to hit the location, even if the current url is a
     * different one.
     *
     * @param $historicUrl
     * @param $locationId
     * @return boolean
     */
    public function createUrlHistoryEntry( $historicUrl, $locationId );

    /**
     * List of url entries of $urlType, pointing to $locationId.
     *
     * @param $locationId
     * @param $urlType
     * @return mixed
     */
    public function listUrlsForLocation( $locationId, $urlType );

    /**
     * Removes urls pointing to $locationId, identified by the element in $urlIdentifier.
     *
     * @param $locationId
     * @param array $urlIdentifier
     * @return boolean
     */
    public function removeUrlsForLocation( $locationId, array $urlIdentifier );

    /**
     * Returns the full url alias to $locationId from /.
     *
     * @param $locationId
     * @param $languageCode
     * @return string
     */
    public function getPath( $locationId, $languageCode );
}
?>
