<?php
/**
 * File containing the RepositoryHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\persistence;

/**
 * @package ezp.persistence
 */
interface RepositoryHandler 
{

	/**
	 * @return ezp.persistence.content.ContentHandler
	 * @ReturnType ezp.persistence.content.ContentHandler
	 */
	public function contentHandler();

	/**
	 * @return ContentTypeHandler
	 * @ReturnType ContentTypeHandler
	 */
	public function contentTypeHandler();

	/**
	 * @return ezp.persistence.content.LocationHandler
	 * @ReturnType ezp.persistence.content.LocationHandler
	 */
	public function locationHandler();

	/**
	 * @return ezp.persistence.user.UserHandler
	 * @ReturnType ezp.persistence.user.UserHandler
	 */
	public function userHandler();

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