<?php
namespace ezp\persistence\content_types;
/**
 * @package ezp.persistence.content_types
 */
interface ContentTypeHandler 
{

	/**
	 * @param ezp.persistence.content_types.ContentTypeGroup group
	 * @return ezp.persistence.content_types.ContentTypeGroup
	 * @ParamType group ezp.persistence.content_types.ContentTypeGroup
	 * @ReturnType ezp.persistence.content_types.ContentTypeGroup
	 */
	public function createGroup(ContentTypeGroup $group);

	/**
	 * @param ezp.persistence.content_types.ContentTypeGroup group
	 * @ParamType group ezp.persistence.content_types.ContentTypeGroup
	 */
	public function updateGroup(ContentTypeGroup $group);

	/**
	 * @param int grouId
	 * @ParamType grouId int
	 */
	public function deleteGroup($grouId);

	/**
	 * @return array
	 * @ReturnType array
	 */
	public function loadAllGroups();

	/**
	 * @param int groupId
	 * @return array
	 * @ParamType groupId int
	 * @ReturnType array
	 */
	public function loadContentTypes($groupId);

	/**
	 * @param int contentTypeId
	 * @param int version
	 * @ParamType contentTypeId int
	 * @ParamType version int
	 */
	public function load($contentTypeId, $version = 1);

	/**
	 * @param ezp.persistence.content_types.ContentType contentTyoe
	 * @return ezp.persistence.content_types.ContentType
	 * @ParamType contentTyoe ezp.persistence.content_types.ContentType
	 * @ReturnType ezp.persistence.content_types.ContentType
	 */
	public function create(ContentType $contentTyoe);

	/**
	 * @param ezp.persistence.content_types.ContentType contentTyoe
	 * @ParamType contentTyoe ezp.persistence.content_types.ContentType
	 */
	public function update(ContentType $contentTyoe);

	/**
	 * @param int contentTypeId
	 * @ParamType contentTypeId int
	 */
	public function delete($contentTypeId);

	/**
	 * @param int userId
	 * @param int contentTypeId
	 * @param int version
	 * @ParamType userId int
	 * @ParamType contentTypeId int
	 * @ParamType version int
	 */
	public function createVersion($userId, $contentTypeId, $version);

	/**
	 * @param int userId
	 * @param int contentTypeId
	 * @return ezp.persistence.content_types.ContentType
	 * @ParamType userId int
	 * @ParamType contentTypeId int
	 * @ReturnType ezp.persistence.content_types.ContentType
	 */
	public function copy($userId, $contentTypeId);

	/**
	 * @param int groupId
	 * @param int contentTypeId
	 * @ParamType groupId int
	 * @ParamType contentTypeId int
	 */
	public function unlink($groupId, $contentTypeId);

	/**
	 * @param int contentTypeId
	 * @param int groupId
	 * @ParamType contentTypeId int
	 * @ParamType groupId int
	 */
	public function addGroup($contentTypeId, $groupId);
}
?>