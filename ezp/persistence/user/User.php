<?php
namespace ezp\persistence\user;
/**
 * @access public
 * @package ezp.persistence.user
 */
class User 
{
	/**
	 * @AttributeType int
	 */
	public $id;
	/**
	 * @AttributeType string
	 */
	public $login;
	/**
	 * @AttributeType string
	 */
	public $pwd;
	/**
	 * @AttributeType string
	 */
	public $hashAlg;
	/**
	 * @AssociationType ezp.persistence.content_types.ContentTypeBase
	 */
	public $unnamed_ContentTypeBase_;
	/**
	 * @AssociationType ezp.persistence.content_types.ContentTypeBase
	 */
	public $unnamed_ContentTypeBase_2;
}
?>