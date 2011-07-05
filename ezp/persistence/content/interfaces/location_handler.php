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
 * @package ezp
 * @subpackage persistence_content
 */
interface LocationHandlerInterface
{

	/**
	 * @param $ref
	 * @return \ezp\persistence\content\values\Location
     * @todo: Either define $ref type or switch to 'int $id'
	 */
	public function load( $ref );

	/**
	 * @param int $id
	 */
	public function delete( $id );

	/**
	 * @param int $srcId
	 * @param int $destId
	 */
	public function copy( $srcId, $destId );

	/**
	 * @param int $srcId
	 * @param int $destId
	 */
	public function move( $srcId, $destId );

	/**
	 * @param int $id
	 */
	public function hide( $id );

	/**
	 * @param int $id
	 */
	public function unHide( $id );

	/**
	 * @param int $locationId1
	 * @param int $locationId2
	 */
	public function swap( $locationId1, $locationId2 );

	/**
	 * @param \ezp\persistence\content\values\Location $location
	 */
	public function update( \ezp\persistence\content\values\Location $location );

	/**
	 * @param int $contentId
	 * @param int $parentId
	 * @return \ezp\persistence\content\values\Location
	 */
	public function createLocation( $contentId, $parentId );

	/**
	 * @param int $contentId
	 * @param int $locationId
	 */
	public function removeLocation( $contentId, $locationId );

	/**
	 * @param string $path
	 * @param string $action
	 * @param string $languageName
	 * @param null|int $linkId
	 * @param bool $alwaysAvailable
	 */
	public function storeUrlAliasPath( $path, $action, $languageName, $linkId = null, $alwaysAvailable = false );

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
