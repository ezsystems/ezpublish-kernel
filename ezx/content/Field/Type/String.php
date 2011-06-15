<?php
/**
 * String Field model object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * Float Field value object class
 */
namespace ezx\content;
class Field_Type_String extends Abstract_FieldType
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezstring';

    /**
     * @public
     * @var string
     */
    protected $default = '';

    /**
     * @public
     * @var int
     */
    protected $max_length = 255;

    /**
     * Definition of properties on this class
     *
     * {@inheritdoc}
     *
     * @see \ezx\base\Abstract_Model::$definition
     * @var array
     */
    protected static $definition = array(
        'default' => array(
            'type' => self::TYPE_STRING,
            'legacy_column' => 'data_text1',
        ),
        'max_length' => array(
            'type' => self::TYPE_INT,
            'legacy_column' => 'data_int1',
            'min' => 1,
            'max' => 255,
        ),
    );

    /**
     * Sets identifier on design override and calls parent __construct.
     */
    public function __construct()
    {
        $this->types[] = self::FIELD_IDENTIFIER;
        parent::__construct();
    }
}