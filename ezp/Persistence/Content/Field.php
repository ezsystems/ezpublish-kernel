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
     * Field ID
     *
     * @var mixed
     */
    public $id;

    /**
     * Corresponding field definition
     *
     * @var mixed
     */
    public $fieldDefinitionId;

    /**
     * Data type name.
     *
     * @var string
     */
    public $type;

    /**
     * Value of the field
     *
     * @var FieldValue
     */
    public $value;

    /**
     * @todo What is supposed to be stored here?
     */
    public $language;

    /**
     * @var int|null Null if not created yet
     * @todo Normally we would use a create struct here
     */
    public $versionNo;
}
?>
