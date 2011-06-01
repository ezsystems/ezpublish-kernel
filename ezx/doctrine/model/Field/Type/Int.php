<?php
/**
 * Int Field model object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * Int Field value object class
 */
namespace ezx\doctrine\model;
class Field_Type_Int extends Abstract_Field_Value
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezinteger';

    /**
     * Sets identifier on design override and calls parent __construct.
     */
    public function __construct()
    {
        $this->types[] = self::FIELD_IDENTIFIER;
        parent::__construct();
    }

    static public function definition()
    {
        return array(
            'type' => self::TYPE_INT,
            'legacy_column' => 'data_int1'
        );
    }

    /**
     * @var int
     */
    public $value = 0;
}
