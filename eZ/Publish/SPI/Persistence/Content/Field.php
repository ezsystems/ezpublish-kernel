<?php
/**
 * File containing the (content) Field class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\ValueObject;

/**
 */
class Field extends ValueObject
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
     * @var \ezp\Persistence\Content\FieldValue
     */
    public $value;

    /**
     * @todo What is supposed to be stored here? Object or languageCode?
     */
    public $language;

    /**
     * @var int|null Null if not created yet
     * @todo Normally we would use a create struct here
     */
    public $versionNo;

    public function __clone()
    {
        $this->id = null;
        if ( isset( $this->value ) )
            $this->value = clone $this->value;
    }
}
?>
