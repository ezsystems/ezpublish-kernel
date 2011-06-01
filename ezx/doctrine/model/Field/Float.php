<?php
/**
 * Float Field model object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * Float Field value object class
 */
namespace ezx\doctrine\model;
class Field_Float extends Abstract_Field_Value
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezfloat';

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
            'type' => self::TYPE_FLOAT,
            'legacy_column' => 'data_float'
        );
    }

    /**
     * Called when content object is created the first time
     *
     * @param Field_Type_Float $contentTypeFieldValue
     * @return Field_Float
     */
    public function init( Interface_Value $contentTypeFieldValue )
    {

    }

    /**
     * @var float
     */
    protected $value = 0.0;
}
