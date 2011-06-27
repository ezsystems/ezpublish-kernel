<?php
namespace ezp\persistence;
/**
 * @access public
 * @author root
 * @package ezp.persistence
 */
interface RepositoryHandler 
{

	/**
	 * @access public
	 * @return ezp.persistence.content.ContentHandler
	 * @ReturnType ezp.persistence.content.ContentHandler
	 */
	public function contentHandler();

	/**
	 * @access public
	 * @return ContentTypeHandler
	 * @ReturnType ContentTypeHandler
	 */
	public function contentTypeHandler();

	/**
	 * @access public
	 * @return ezp.persistence.content.LocationHandler
	 * @ReturnType ezp.persistence.content.LocationHandler
	 */
	public function locationHandler();

	/**
	 * @access public
	 * @return ezp.persistence.user.UserHandler
	 * @ReturnType ezp.persistence.user.UserHandler
	 */
	public function userHandler();

	/**
	 * @access public
	 */
	public function beginTransaction();

	/**
	 * @access public
	 */
	public function commit();

	/**
	 * @access public
	 */
	public function rollback();
}
?>