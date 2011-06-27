<?php
namespace ezp\persistence\content;
/**
 * @access public
 * @package ezp.persistence.content
 */
interface ContentHandler 
{

	/**
	 * @access public
	 * @param ezp.persistence.content.values.ContentCreateStruct content
	 * @return ezp.persistence.content.values.Content
	 * @ParamType content ezp.persistence.content.values.ContentCreateStruct
	 * @ReturnType ezp.persistence.content.values.Content
	 */
	public function create(ContentCreateStruct $content);

	/**
	 * @access public
	 * @param int contentId
	 * @param int srcVersion
	 * @return ezp.persistence.content.values.Content
	 * @ParamType contentId int
	 * @ParamType srcVersion int
	 * @ReturnType ezp.persistence.content.values.Content
	 */
	public function createDraftFromVersion($contentId, $srcVersion = false);

	/**
	 * @access public
	 * @param int id
	 * @return ezp.persistence.content.values.Content
	 * @ParamType id int
	 * @ReturnType ezp.persistence.content.values.Content
	 */
	public function load($id);

	/**
	 * @access public
	 * @param Criteria criteria
	 * @param limit
	 * @param sort
	 * @ParamType criteria Criteria
	 */
	public function query(Criteria $criteria, $limit, $sort);

	/**
	 * @access public
	 * @param int contentId
	 * @param int state
	 * @param int version
	 * @ParamType contentId int
	 * @ParamType state int
	 * @ParamType version int
	 */
	public function setState($contentId, $state, $version);

	/**
	 * @access public
	 * @param ezp.persistence.content.values.ContentUpdateStruct content
	 * @ParamType content ezp.persistence.content.values.ContentUpdateStruct
	 */
	public function update(ContentUpdateStruct $content);

	/**
	 * Deletes all versions and fields, all locations (subtree), all relations
	 * @access public
	 * @param int contentId
	 * @ParamType contentId int
	 */
	public function delete($contentId);

	/**
	 * @access public
	 * @param int contentId
	 * @ParamType contentId int
	 */
	public function trash($contentId);

	/**
	 * @access public
	 * @param int contentId
	 * @ParamType contentId int
	 */
	public function untrash($contentId);

	/**
	 * @access public
	 * @param int contentId
	 * @return array
	 * @ParamType contentId int
	 * @ReturnType array
	 */
	public function listVersions($contentId);

	/**
	 * @access public
	 * @param int contentId
	 * @param string languageCode
	 * @return Content
	 * @ParamType contentId int
	 * @ParamType languageCode string
	 * @ReturnType Content
	 */
	public function fetchTranslation($contentId, $languageCode);
}
?>