<?php
/**
 * Int Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Type\Field;
use ezp\Content\AbstractFieldType,
    ezp\Content\Interfaces\ContentFieldDefinition;
/**
 * Int Field value object class
 */
class Int extends AbstractFieldType implements ContentFieldDefinition
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
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'min' => 'data_int1',
        'max' => 'data_int2',
        'default' => 'data_int3',
        'state' => 'data_int4',
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
