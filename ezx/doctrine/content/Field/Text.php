<?php
/**
 * Image Field model object
 *
 * @copyright Copyright (c) 2011, eZ Systems AS
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ext
 * @subpackage doctrine
 */

/**
 * Image Field value object class
 */
namespace ezx\doctrine\content;
class Field_Text extends Field_String
{
    /**
     * Field type identifier
     * @var string
     */
    const FIELD_IDENTIFIER = 'eztext';

    /**
     * Sets identifier on design override and calls parent __construct.
     */
    public function __construct()
    {
        $this->types[] = self::FIELD_IDENTIFIER;
        parent::__construct();
    }
}
