<?php
/**
 * File containing the ezp\content\Section class.
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
namespace ezp\content;
class Section extends \ezp\base\AbstractModel
{

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

    /**
     * Id of the section
     *
     * @var int
     */
    protected $id = 0;

    public function __construct()
    {
        $this->readableProperties = array(
            "id" => true,
        );

    }

}

?>
