<?php
/**
 * File containing the UpdateStruct struct
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\ValueObject;

/**
 */
class UpdateStruct extends ValueObject
{
    /**
     * @todo Which version is this?
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $userId;

    /**
     * Contains fields to be updated.
     *
     * @var array(Field)
     */
    public $fields = array();
}
?>
