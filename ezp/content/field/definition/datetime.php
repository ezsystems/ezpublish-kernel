<?php
/**
 * Relation Field domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage content
 */

/**
 * Relation Field value object class
 */
namespace ezp\content\Field\Definition;
class Datetime extends Int
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezdatetime';

    /**
     * @var int
     */
    public $default = 0;

    /**
     * @var int
     */
    public $useSeconds = 0;

    /**
     * @var string
     */
    public $adjustment = 0;

    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'default' => 'data_int1',
        'useSeconds' => 'data_int2',
        'adjustment' => 'data_text5',
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
