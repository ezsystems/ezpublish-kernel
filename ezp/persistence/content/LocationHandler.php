<?php
namespace ezp\persistence\content;
/**
 * @access public
 * @package ezp.persistence.content
 */
interface LocationHandler 
{

	/**
	 * @access public
	 * @param ref
	 * @return ezp.persistence.content.values.Location
	 * 
	 * @ReturnType ezp.persistence.content.values.Location
	 */
	public function load($ref);

	/**
	 * @access public
	 * @param id int
	 * @ParamType int id
	 */
	public function delete(id $int);

	/**
	 * @access public
	 * @param int srcId
	 * @param int destId
	 * @ParamType srcId int
	 * @ParamType destId int
	 */
	public function copy($srcId, $destId);

	/**
	 * @access public
	 * @param int srcId
	 * @param int destId
	 * @ParamType srcId int
	 * @ParamType destId int
	 */
	public function move($srcId, $destId);

	/**
	 * @access public
	 * @param int id
	 * @ParamType id int
	 */
	public function hide($id);

	/**
	 * @access public
	 * @param int id
	 * @ParamType id int
	 */
	public function unHide($id);

	/**
	 * @access public
	 * @param int locationId1
	 * @param int locationId2
	 * @ParamType locationId1 int
	 * @ParamType locationId2 int
	 */
	public function swap($locationId1, $locationId2);

	/**
	 * @access public
	 * @param ezp.persistence.content.values.Location location
	 * @ParamType location ezp.persistence.content.values.Location
	 */
	public function update(Location $location);

	/**
	 * @access public
	 * @param int contentId
	 * @param int parentId
	 * @return ezp.persistence.content.values.Location
	 * @ParamType contentId int
	 * @ParamType parentId int
	 * @ReturnType ezp.persistence.content.values.Location
	 */
	public function createLocation($contentId, $parentId);

	/**
	 * @access public
	 * @param int contentId
	 * @param int locationId
	 * @ParamType contentId int
	 * @ParamType locationId int
	 */
	public function removeLocation($contentId, $locationId);

	/**
	 * @access public
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
	 * @access public
	 * @param string languageCode
	 * @ParamType languageCode string
	 */
	public function getPath($languageCode);

	/**
	 * @access public
	 * @param string actionName
	 * @param array actionValues
	 * @ParamType actionName string
	 * @ParamType actionValues array
	 */
	public function getPathByActionList($actionName, array_51 $actionValues);
}
?>