<?php
namespace ezp\persistence\content_types;
/**
 * @package ezp.persistence.content_types
 */
class ContentTypeBase extends TypeBase 
{
	/**
	 * @AttributeType int
	 */
	public $created;
	/**
	 * @AttributeType int
	 */
	public $modified;
	/**
	 * @AssociationType ezp.persistence.user.User
	 * @AssociationMultiplicity 1
	 * @AssociationKind Aggregation
	 */
	public $creator;
	/**
	 * @AssociationType ezp.persistence.user.User
	 * @AssociationMultiplicity 1
	 * @AssociationKind Aggregation
	 */
	public $modifier;
}
?>