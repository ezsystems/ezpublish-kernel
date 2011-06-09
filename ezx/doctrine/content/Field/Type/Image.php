<?php
/**
 * Image Field model object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * Image Field value object class
 */
namespace ezx\doctrine\content;
class Field_Type_Image extends Field_Type_String
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezimage';

    /**
     * @var int
     */
    protected $max_size = 0;

    /**
     * Definition of properties on this class
     *
     * {@inheritdoc}
     *
     * @see \ezx\doctrine\Abstract_Model::$definition
     * @var array
     */
    protected static $definition = array(
        'max_size' => array(
            'type' => self::TYPE_INT,
            'legacy_column' => 'data_int1',
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
