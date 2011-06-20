<?php
/**
 * Relation Field domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * Relation Field value object class
 */
namespace ezx\content;
class Field_Type_Datetime extends Field_Type_Int
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezdatetime';

    /**
     * @var int
     */
    protected $default = 0;

    /**
     * @var int
     */
    protected $use_seconds = 0;

    /**
     * @var string
     */
    protected $adjustment = 0;

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
            'legacy_column' => 'data_int1',
        ),
        'use_seconds' => array(
            'type' => self::TYPE_INT,
            'legacy_column' => 'data_int2',
        ),
        'adjustment' => array(
            'type' => self::TYPE_STRING,
            'legacy_column' => 'data_text5',
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
