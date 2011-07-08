<?php
/**
 * File containing the ezp\Content\Section class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */

/**
 * This class represents a Section
 *
 * @package ezp
 * @subpackage content
 */
namespace ezp\Content;
class Section extends \ezp\Base\AbstractModel
{
    protected $readableProperties = array(
        'id' => false,
        'identifier' => true,
        'name' => true,
    );

    /**
     * Id of the section
     *
     * @var int
     */
    protected $id = 0;

    /**
     * Identifier of the section
     *
     * @var string
     */
    public $identifier = "";

    /**
     * Name of the section
     *
     * @var string
     */
    public $name = "";

    public function __construct()
    {
    }

}

?>
