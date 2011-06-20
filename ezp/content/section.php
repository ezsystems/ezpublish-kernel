<?php
/**
 * File containing the ezp\Content\Section class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage Content
 */

/**
 * This class represents a Section
 *
 * @package API
 * @subpackage Content
 */
namespace ezp\Content;

class Section extends Base implements \ezp\DomainObjectInterface
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

    public function __construct()
    {
        $this->properties = array(
            "id" => false,
        );

    }

}

?>
