<?php
/**
 * File containing the ContentTypeGroup class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Type;
use ezp\Persistence\AbstractValueObject;

/**
 */
class Group extends AbstractValueObject
{
    /**
     * Primary key
     *
     * @var mixed
     */
    public $id;

    /**
     * Name
     *
     * @var string[]
     */
    public $name;

    /**
     * Description
     *
     * @var string[]
     */
    public $description;

    /**
     * Readable string identifier of a group
     *
     * @var string
     */
    public $identifier;

    /**
     * Created date (timestamp)
     *
     * @var int
     */
    public $created;

    /**
     * Modified date (timestamp)
     *
     * @var int
     */
    public $modified;

    /**
     * Creator user id
     *
     * @var mixed
     */
    public $creator;

    /**
     * Modifier user id
     *
     * @var mixed
     *
     */
    public $modifier;

    /**
     * @var array(Content\Type)
     */
    public $contentTypes = array();
}
?>
