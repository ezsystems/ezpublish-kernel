<?php
/**
 * File containing the Author class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Author;
use ezp\Persistence\ValueObject;

/**
 * Description of Author
 */
class Author extends ValueObject
{
    /**
     * Author's Id in the collection that holds it
     *
     * @var int
     */
    public $id;

    /**
     * Name of the author
     *
     * @var string
     */
    public $name;

    /**
     * Email of the author
     *
     * @var string
     */
    public $email;
}
