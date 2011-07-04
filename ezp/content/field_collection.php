<?php
/**
 * File contains Field Collection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2.0
 * @package ezp
 * @subpackage content
 */

/**
 * Field Collection class
 *
 * Readonly class that takes (Content) Version as input.
 *
 * @package ezp
 * @subpackage content
 */
namespace ezp\content;
class FieldCollection extends \ezp\base\ReadOnlyCollection
{
    /**
     * Constructor, sets up FieldCollection based on contentType fields
     *
     * @todo Handle translations
     * @param Version $contentVersion
     */
    public function __construct( Version $contentVersion )
    {
        $elements = array();
        $this->count = 0;
        foreach ( $contentVersion->content->contentType->fields as $contentTypeField )
        {
            $elements[ $contentTypeField->identifier ] = $field = new Field( $contentVersion, $contentTypeField );
            $contentVersion->attach( $field, 'store' );
            $this->count++;
        }
        parent::__construct( $elements );
    }

    /**
     * Set value on a offset in collection, only allowed on existing items where value is forwarded to ->type->value
     *
     * @internal
     * @throws \InvalidArgumentException When trying to set new values / append
     * @param string|int $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        if ( $offset === null || !isset( $this->elements[$offset] ) )
            throw new \InvalidArgumentException( "FieldCollection is locked and offset:{$offset} can not be appended!" );
        $this->elements[$offset]->type->value = $value;
    }
}

?>
