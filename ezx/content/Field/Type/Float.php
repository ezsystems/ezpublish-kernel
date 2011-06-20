<?php
/**
 * Float Field domain object
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
class Field_Type_Float extends Abstracts\FieldType
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezfloat';

    /**
     * @var float
     */
    protected $default = 0.0;

    /**
     * @var float
     */
    protected $min = 0.0;

    /**
     * @var float
     */
    protected $max = 0.0;

    /**
     * @var float
     */
    protected $state = 0;

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
            'type' => self::TYPE_FLOAT,
            'legacy_column' => 'data_float3',
        ),
        'min' => array(
            'type' => self::TYPE_FLOAT,
            'legacy_column' => 'data_float1',
        ),
        'max' => array(
            'type' => self::TYPE_FLOAT,
            'legacy_column' => 'data_float2',
        ),
        'state' => array(
            'type' => self::TYPE_FLOAT,
            'legacy_column' => 'data_float4',
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
