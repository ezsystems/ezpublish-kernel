<?php
/**
 * File containing the ContentHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence\content;

/**
 * @package ezp.persistence.content
 */
interface ContentHandler 
{

	/**
	 * @param ezp.persistence.content.values.ContentCreateStruct content
	 * @return ezp.persistence.content.values.Content
	 * @ParamType content ezp.persistence.content.values.ContentCreateStruct
	 * @ReturnType ezp.persistence.content.values.Content
	 */
	public function create(\ezp\persistence\content\values\ContentCreateStruct $content);

	/**
	 * @param int contentId
	 * @param int srcVersion
	 * @return ezp.persistence.content.values.Content
	 * @ParamType contentId int
	 * @ParamType srcVersion int
	 * @ReturnType ezp.persistence.content.values.Content
	 */
	public function createDraftFromVersion($contentId, $srcVersion = false);

	/**
	 * @param int id
	 * @return ezp.persistence.content.values.Content
	 * @ParamType id int
	 * @ReturnType ezp.persistence.content.values.Content
	 */
	public function load($id);

	/**
	 * @param Criteria criteria
	 * @param limit
	 * @param sort
	 * @ParamType criteria Criteria
	 */
	public function query(\ezp\content\Criteria\Criteria $criteria, $limit, $sort);

	/**
	 * @param int contentId
	 * @param int state
	 * @param int version
	 * @ParamType contentId int
	 * @ParamType state int
	 * @ParamType version int
	 */
	public function setState($contentId, $state, $version);

	/**
	 * @param ezp.persistence.content.values.ContentUpdateStruct content
	 * @ParamType content ezp.persistence.content.values.ContentUpdateStruct
	 */
	public function update(\ezp\persistence\content\values\ContentUpdateStruct $content);

	/**
	 * Deletes all versions and fields, all locations (subtree), all relations
	 * @param int contentId
	 * @ParamType contentId int
	 */
	public function delete($contentId);

	/**
	 * @param int contentId
	 * @ParamType contentId int
	 */
	public function trash($contentId);

	/**
	 * @param int contentId
	 * @ParamType contentId int
	 */
	public function untrash($contentId);

	/**
	 * @param int contentId
	 * @return array
	 * @ParamType contentId int
	 * @ReturnType array
	 */
	public function listVersions($contentId);

	/**
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