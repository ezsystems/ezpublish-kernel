<?php
/**
 * Relation Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

/**
 * Relation Field value object class
 */
namespace ezp\Content\Type\Field;
class Boolean extends Int
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezboolean';

    /**
     * @var int
     */
    public $default = 0;

    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'default' => 'data_int1',
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
