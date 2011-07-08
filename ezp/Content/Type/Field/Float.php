<?php
/**
 * Float Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage content
 */

/**
 * Float Field value object class
 */
namespace ezp\Content\Type\Field;
class Float extends \ezp\Content\AbstractFieldType implements \ezp\Content\Interfaces\ContentFieldDefinition
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezfloat';

    /**
     * @var float
     */
    public $default = 0.0;

    /**
     * @var float
     */
    public $min = 0.0;

    /**
     * @var float
     */
    public $max = 0.0;

    /**
     * @var float
     */
    public $state = 0;

    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'min' => 'data_float1',
        'max' => 'data_float2',
        'default' => 'data_float3',
        'state' => 'data_float4',
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
