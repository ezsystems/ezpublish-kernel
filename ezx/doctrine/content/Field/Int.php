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
namespace ezx\doctrine\content;
class Field_Int extends Abstract_FieldType implements Interface_Field_Init
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

    /**
     * Definition of properties on this class
     *
     * {@inheritdoc}
     *
     * @see \ezx\doctrine\Abstract_Model::$definition
     * @var array
     */
    protected static $definition = array(
        'value' => array(
            'type' => self::TYPE_INT,
            'legacy_column' => 'data_int',
        ),
    );

    /**
     * Called when content object is created the first time
     *
     * @param Field_Type_Int $contentTypeFieldValue
     * @return Field_Int
     */
    public function init( Abstract_FieldType $contentTypeFieldValue )
    {
        return $this;
    }

    /**
     * @var int
     */
    protected $value = 0;
}
