<?php
/**
 * String Field model object
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
class Field_String extends Abstract_FieldValue
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezstring';

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
            'type' => self::TYPE_STRING,
            'legacy_column' => 'data_text'
        ),
    );

    /**
     * Called when content object is created the first time
     *
     * @param Field_Type_String $contentTypeFieldValue
     * @return Field_String
     */
    public function init( \ezx\doctrine\Interface_Value $contentTypeFieldValue )
    {
        $this->setValue( $contentTypeFieldValue->getValue() );
        return $this;
    }

    /**
     * @var string
     */
    protected $value = '';
}