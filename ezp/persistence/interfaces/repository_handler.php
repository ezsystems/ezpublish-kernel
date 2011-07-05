<?php
/**
 * File containing the RepositoryHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage persistence
 */

namespace ezp\persistence;

/**
 * The main handler for Storage Engine
 *
 * @package ezp
 * @subpackage persistence
 */
interface RepositoryHandlerInterface
{
	/**
	 * @return \ezp\persistence\content\ContentHandlerInterface
	 */
	public function contentHandler();

	/**
	 * @return \ezp\persistence\content_types\ContentTypeHandlerInterface
	 */
	public function contentTypeHandler();

	/**
	 * @return \ezp\persistence\content\LocationHandlerInterface
	 */
	public function locationHandler();

	/**
	 * @return \ezp\persistence\user\UserHandlerInterface
	 */
	public function userHandler();

	/**
	 * @return \ezp\persistence\content\SectionHandlerInterface
	 */
	public function sectionHandler();

	/**
	 */
	public function beginTransaction();

	/**
	 */
	public function commit();

	/**
	 */
	public function rollback();
}
?>
