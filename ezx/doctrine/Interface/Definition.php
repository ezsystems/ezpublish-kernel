<?php
/**
 * Definition interface
 * Defines attributes on an object for use by renderes, serializers and other mappers.
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */
namespace ezx\doctrine;
interface Interface_Definition
{
    //protected static $definition;

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
     */
    const TYPE_ARRAY  = 4;

    /**
     * Constant for object type in {@see definition()}
     * @var int
     */
    const TYPE_OBJECT = 5;

    /**
     * Constant for bool type in {@see definition()}
     * @var int
     */
    const TYPE_BOOL   = 6;

    /**
     * Class definition, used for various mappings
     *
     * - Simple example:
     *  array(
     *      'value' => array(
     *          'type' => self::TYPE_STRING,
     *          'legacy_column' => 'data_text'
     *      ),
     *  );
     *
     * - In the case of several values, for instance for rating:
     *  array(
     *      'enable_rating' => array(
     *          'type' => self::TYPE_BOOL,
     *          'legacy_column' => 'data_int'
     *      ),
     *      'disable_on_date' => array(
     *          'type' => self::TYPE_DATE,
     *          'legacy_column' => 'data_text'
     *      ),
     *  );
     *
     * - Or for field types:
     *  array(
     *      'max_length' => array(
     *          'type' => self::TYPE_INT,
     *          'legacy_column' => 'data_int1'
     *      ),
     *      'default' => array(
     *          'type' => self::TYPE_STRING,
     *          'legacy_column' => 'data_text1'
     *      ),
     *  );
     *
     * @return array
     */
    public static function definition();
}