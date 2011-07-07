<?php
/**
 * File containing the ContentTypeHandler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage persistence_content_type
 */

namespace ezp\persistence\content\type;

/**
 * @package ezp
 * @subpackage persistence_content_type
 */
interface HandlerInterface extends \ezp\persistence\ServiceHandlerInterface
{
	/**
	 * @param Group $group
	 * @return Group
	 */
	public function createGroup( Group $group );

	/**
	 * @param Group $group
	 */
	public function updateGroup( Group $group );

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
	 * @param Type $contentType
	 * @return Type
	 */
	public function create( Type $contentType );

	/**
	 * @param Type $contentType
	 */
	public function update( Type $contentType );

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
	 * @return Type
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
