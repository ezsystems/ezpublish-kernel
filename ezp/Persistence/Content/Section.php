<?php
/**
 * File containing the Section class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\AbstractValueObject;

/**
 */
class Section extends AbstractValueObject
{
    /**
     * Id of the section
     *
     * @var int
     */
    public $id;

    /**
     * Unique identifier of the section
     *
     * @var string
     */
    public $identifier;

    /**
     * Name of the section
     *
     * @var string
     */
    public $name;
}
?>
