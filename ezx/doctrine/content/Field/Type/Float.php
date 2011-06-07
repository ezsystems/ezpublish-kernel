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
namespace ezx\doctrine\content;
class Field_Type_Float extends Abstract_FieldValue
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

    protected static $definition = array(
        'value' => array(
            'type' => self::TYPE_FLOAT,
            'legacy_column' => 'data_float1'
        ),
    );

    /**
     * @var float
     */
    protected $value = 0.0;
}
