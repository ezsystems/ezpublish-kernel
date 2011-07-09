<?php
/**
 * Image Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @package ezp
 * @subpackage content
 */

/**
 * Image Field value object class
 */
namespace ezp\Content\Type\Field;
class Image extends String
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezimage';

    /**
     * @var int
     */
    public $maxSize = 0;

    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'maxSize' => 'data_int1',
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
