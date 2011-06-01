<?php
/**
 * Value object interface
 *
 * @todo Maybe move defintion stuff into it's own Interface_Defintion for re use by Interface_Renderable and others
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */
namespace ezx\doctrine\model;
interface Interface_Value
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
     * @deprecated Do not use atm, use case not defined, possible use case shown in {@see definition()}
     */
    const TYPE_ARRAY  = 4;

    /**
     * Constant for object type in {@see definition()}
     * @var int
     * @deprecated Do not use atm, use case not defined, could hint that type is another object.
     */
    const TYPE_OBJECT = 5;

    /**
     * Constant for bool type in {@see definition()}
     * @var int
     */
    const TYPE_BOOL   = 6;

    /**
     * Set value
     *
     * @param mixed $value As defined by defintion()
     * @return Interface_Value
     */
    public function setValue( $value );

    /**
     * Get value
     *
     * @return mixed As defined by defintion()
     */
    public function getValue();

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
            'type' => self::TYPE_ARRAY,
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
            'type' => self::TYPE_ARRAY,
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