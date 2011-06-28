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
	 * @ReturnType ezp.persistence.content.values.Location
	 */
	public function load($ref);

	/**
	 * @param int
	 * @ParamType int id
	 */
	public function delete($int);

	/**
	 * @param int srcId
	 * @param int destId
	 * @ParamType srcId int
	 * @ParamType destId int
	 */
	public function copy($srcId, $destId);

	/**
	 * @param int srcId
	 * @param int destId
	 * @ParamType srcId int
	 * @ParamType destId int
	 */
	public function move($srcId, $destId);

	/**
	 * @param int id
	 * @ParamType id int
	 */
	public function hide($id);

	/**
	 * @param int id
	 * @ParamType id int
	 */
	public function unHide($id);

	/**
	 * @param int locationId1
	 * @param int locationId2
	 * @ParamType locationId1 int
	 * @ParamType locationId2 int
	 */
	public function swap($locationId1, $locationId2);

	/**
	 * @param ezp.persistence.content.values.Location location
	 * @ParamType location ezp.persistence.content.values.Location
	 */
	public function update(\ezp\content\Services\Location $location);

	/**
	 * @param int contentId
	 * @param int parentId
	 * @return ezp.persistence.content.values.Location
	 * @ParamType contentId int
	 * @ParamType parentId int
	 * @ReturnType ezp.persistence.content.values.Location
	 */
	public function createLocation($contentId, $parentId);

	/**
	 * @param int contentId
	 * @param int locationId
	 * @ParamType contentId int
	 * @ParamType locationId int
	 */
	public function removeLocation($contentId, $locationId);

	/**
	 * @param string path
	 * @param string action
	 * @param string languageName
	 * @param int linkId
	 * @param boolean alwaysAvailable
	 * @ParamType path string
	 * @ParamType action string
	 * @ParamType languageName string
	 * @ParamType linkId int
	 * @ParamType alwaysAvailable boolean
	 */
	public function storeUrlAliasPath($path, $action, $languageName, $linkId = false, $alwaysAvailable = false);

	/**
	 * @param string languageCode
	 * @ParamType languageCode string
	 */
	public function getPath($languageCode);

	/**
	 * @param string actionName
	 * @param array actionValues
	 * @ParamType actionName string
	 * @ParamType actionValues array
	 */
	public function getPathByActionList($actionName, array $actionValues);
}
?>