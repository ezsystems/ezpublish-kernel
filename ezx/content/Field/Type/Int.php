<?php
/**
 * Int Field domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * Int Field value object class
 */
namespace ezx\content;
class Field_Type_Int extends Abstracts\FieldType
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezinteger';

    /**
     * @var int
     */
    public $default = 0;

    /**
     * @var int
     */
    public $min = 0;

    /**
     * @var int
     */
    public $max = 0;

    /**
     * @var int
     */
    public $state = 0;

    /**
     * Definition of properties on this class
     *
     * {@inheritdoc}
     *
     * @see \ezx\base\Abstracts\DomainObject::$definition
     * @var array
     */
    protected static $definition = array(
        'default' => array(
            'type' => self::TYPE_INT,
            'legacy_column' => 'data_int3',
        ),
        'min' => array(
            'type' => self::TYPE_INT,
            'legacy_column' => 'data_int1',
        ),
        'max' => array(
            'type' => self::TYPE_INT,
            'legacy_column' => 'data_int2',
        ),
        'state' => array(
            'type' => self::TYPE_INT,
            'legacy_column' => 'data_int4',
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
