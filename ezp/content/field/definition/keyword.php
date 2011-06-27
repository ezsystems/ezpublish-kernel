<?php
/**
 * Keyword Field domain object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage content
 */

/**
 * Keyword Field value object class
 */
namespace ezp\content\Field\Definition;
class Keyword extends String
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'ezkeyword';

    /**
     * @public
     * @var string
     */
    public $default = '';

    /**
     * @var array Readable of properties on this object
     */
    protected $readableProperties = array(
        'default' => 'data_text1',
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
