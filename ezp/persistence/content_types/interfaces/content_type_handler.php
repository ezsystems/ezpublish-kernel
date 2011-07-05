<?php
/**
 * File containing the ContentTypeHandler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage persistence_content_types
 */

namespace ezp\persistence\content_types;

/**
 * @package ezp
 * @subpackage persistence_content_types
 */
interface ContentTypeHandlerInterface extends \ezp\persistence\ServiceHandlerInterface
{
	/**
	 * @param ContentTypeGroup $group
	 * @return ContentTypeGroup
	 */
	public function createGroup( ContentTypeGroup $group );

	/**
	 * @param ContentTypeGroup $group
	 */
	public function updateGroup( ContentTypeGroup $group );

	/**
	 * @param int $groupId
	 */
	public function deleteGroup( $groupId );

	/**
	 * @return array
	 */
	public function loadAllGroups();

	/**
	 * @param int $groupId
	 * @return array
	 */
	public function loadContentTypes( $groupId );

	/**
	 * @param int $contentTypeId
	 * @param int $version
     * @todo Use constant for $version?
	 */
	public function load( $contentTypeId, $version = 1 );

	/**
	 * @param ContentType $contentType
	 * @return ContentType
	 */
	public function create( ContentType $contentType );

	/**
	 * @param ContentType $contentType
	 */
	public function update( ContentType $contentType );

	/**
	 * @param int $contentTypeId
	 */
	public function delete( $contentTypeId );

	/**
	 * @param int $userId
	 * @param int $contentTypeId
	 * @param int $version
	 */
	public function createVersion( $userId, $contentTypeId, $version );

	/**
	 * @param int $userId
	 * @param int $contentTypeId
	 * @return ContentType
	 */
	public function copy( $userId, $contentTypeId );

	/**
	 * @param int $groupId
	 * @param int $contentTypeId
	 */
	public function unlink( $groupId, $contentTypeId );

	/**
	 * @param int $contentTypeId
	 * @param int $groupId
	 */
	public function addGroup($contentTypeId, $groupId);
}
?>
