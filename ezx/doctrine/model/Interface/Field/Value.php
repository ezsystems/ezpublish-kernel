<?php
/**
 * Data Field model object interface
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */
namespace ezx\doctrine\model;
interface Interface_Field_Value
{
    //public $value;

    /**
     * Constant for string type in {@see definition()}
     * @var int
     */
    const TYPE_STRING = 1;

    /**
     * Constant for int type in {@see definition()}
     * @var int
     */
    const TYPE_INT    = 2;

    /**
     * Constant for float type in {@see definition()}
     * @var int
     */
    const TYPE_FLOAT  = 3;

    /**
     * Constant for array type in {@see definition()}
     * @var int
     * @deprecated Do not use atm, use case not defined
     */
    const TYPE_ARRAY  = 4;

    /**
     * Constant for object type in {@see definition()}
     * @var int
     * @deprecated Do not use atm, use case not defined
     */
    const TYPE_OBJECT = 5;

    /**
     * Constant for bool type in {@see definition()}
     * @var int
     */
    const TYPE_BOOL   = 6;

    /**
     * Constructor, appends $types
     */
    public function __construct();

    /**
     * Assign $value by reference
     *
     * @param mixed $value As defined by defintion()
     */
    public function assignValue( &$value );

    /**
     * Field definition, Field class will take care of mapping value before initiating field value
     *
     * @return array
     */
    static public function definition();
/*
        // simple example
        return array(
            'type' => self::TYPE_STRING,
            'legacy_column' => 'data_text'
        );
        // in the case of several values, for instance for rating:
        return array(
            'values' => array(
                'enable_rating' => array(
                    'type' => self::TYPE_BOOL,
                    'legacy_column' => 'data_int'
                ),
                'disable_on_date' => array(
                    'type' => self::TYPE_DATE,
                    'legacy_column' => 'data_text'
                ),
             ),
        );
        // or for field types:
        return array(
            'values' => array(
                'max_length' => array(
                    'type' => self::TYPE_INT,
                    'legacy_column' => 'data_int1'
                ),
                'default' => array(
                    'type' => self::TYPE_STRING,
                    'legacy_column' => 'data_text1'
                ),
            ),
        );
*/
}