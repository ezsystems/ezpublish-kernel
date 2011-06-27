<?php
namespace ezp\persistence\fields;
/**
 * @access public
 * @package ezp.persistence.fields
 */
interface StorageInterfacel 
{

	/**
	 * @access public
	 * @return int
	 * @ReturnType int
	 */
	public function typeHint();

	/**
	 * @access public
	 * @param array data
	 * @param ezp.persistence.content.values.ContentField field
	 * @ParamType data array
	 * @ParamType field ezp.persistence.content.values.ContentField
	 */
	public function setValue(array_112 $data, ContentField $field);

	/**
	 * @access public
	 * @param int filedId
	 * @param value
	 * @return boolean
	 * @ParamType filedId int
	 * 
	 * @ReturnType boolean
	 */
	public function storeFieldData($filedId, $value);

	/**
	 * @access public
	 * @param int fieldId
	 * @ParamType fieldId int
	 */
	public function getFieldData($fieldId);

	/**
	 * @access public
	 * @param array fieldId
	 * @return boolean
	 * @ParamType fieldId array
	 * @ReturnType boolean
	 */
	public function deleteFieldData(array_113 $fieldId);

	/**
	 * @access public
	 * @return bool
	 * @ReturnType bool
	 */
	public function hasFieldData();

	/**
	 * @access public
	 * @param int fieldId
	 * @ParamType fieldId int
	 */
	public function copyFieldData($fieldId);

	/**
	 * @access public
	 * @param int fieldId
	 * @ParamType fieldId int
	 */
	public function getIndexData($fieldId);
}
?>