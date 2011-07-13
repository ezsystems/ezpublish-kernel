<?php
/**
 * File containing the (content) Field class
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
class Field extends AbstractValueObject
{
    /**
     * @var int
     */
    public $id;

    /**
     * Data type name.
     *
     * @var string
     */
    public $type;

    /**
     * @var FieldValue
     */
    public $value;

    /**
     */
    public $language;

    /**
     * @var int|null Null if not created yet
     */
    public $versionId;
}
?>
