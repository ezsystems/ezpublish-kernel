<?php
/**
 * File containing the LocationHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content;

/**
 * @package ezp.persistence.content
 */
interface LocationHandler 
{

	/**
	 * @param ref
	 * @return ezp.persistence.content.values.Location
	 * 
	 */
	public function load($ref);

	/**
	 * @param int
	 */
	public function delete($int);

	/**
	 * @param int srcId
	 * @param int destId
	 */
	public function copy($srcId, $destId);

	/**
	 * @param int srcId
	 * @param int destId
	 */
	public function move($srcId, $destId);

	/**
	 * @param int id
	 */
	public function hide($id);

	/**
	 * @param int id
	 */
	public function unHide($id);

	/**
	 * @param int locationId1
	 * @param int locationId2
	 */
	public function swap($locationId1, $locationId2);

	/**
	 * @param ezp.persistence.content.values.Location location
	 */
	public function update(\ezp\content\Services\Location $location);

	/**
	 * @param int contentId
	 * @param int parentId
	 * @return ezp.persistence.content.values.Location
	 */
	public function createLocation($contentId, $parentId);

	/**
	 * @param int contentId
	 * @param int locationId
	 */
	public function removeLocation($contentId, $locationId);

	/**
	 * @param string path
	 * @param string action
	 * @param string languageName
	 * @param int linkId
	 * @param boolean alwaysAvailable
	 */
	public function storeUrlAliasPath($path, $action, $languageName, $linkId = false, $alwaysAvailable = false);

	/**
	 * @param string languageCode
	 */
	public function getPath($languageCode);

	/**
	 * @param string actionName
	 * @param array actionValues
	 */
	public function getPathByActionList($actionName, array $actionValues);
}
?>
