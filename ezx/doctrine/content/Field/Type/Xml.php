<?php
/**
 * XML Field model object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * XML Field value object class
 */
namespace ezx\doctrine\content;
class Field_Type_Xml extends Field_Type_Text
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezxmlstring';

    /**
     * @public
     * @var string
     */
    protected $tag_preset = '';

    /**
     * @var int
     */
    protected $columns = 10;

    /**
     * Definition of properties on this class
     *
     * {@inheritdoc}
     *
     * @see \ezx\doctrine\Abstract_Model::$definition
     * @var array
     */
    protected static $definition = array(
        'tag_preset' => array(
            'type' => self::TYPE_STRING,
            'legacy_column' => 'data_text2',
        ),
        'columns' => array(
            'type' => self::TYPE_INT,
            'legacy_column' => 'data_int1',
            'min' => 1,
            'max' => 50,
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
