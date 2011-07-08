<?php
/**
 * XML Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage content
 */

/**
 * XML Field value object class
 */
namespace ezp\Content\Type\Field;
class Xml extends Text
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
    public $tagPreset = '';

    /**
     * @var int
     */
    public $columns = 10;

    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'tagPreset' => 'data_text2',
        'columns' => 'data_int1',
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
