<?php
/**
 * Image Field domain object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage content
 */

/**
 * Image Field value object class
 */
namespace ezx\content;
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
