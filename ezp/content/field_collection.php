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
class FieldCollection extends \ArrayObject implements \ezp\base\CollectionInterface
{
    /**
     * Constructor, sets up FieldCollection based on contentType fields
     *
     * @param Version $contentVersion
     */
    public function __construct( Version $contentVersion )
    {
        $elements = array();
        foreach ( $contentVersion->content->contentType->fields as $contentTypeField )
        {
            $elements[ $contentTypeField->identifier ] = $field = new Field( $contentVersion, $contentTypeField );
            $contentVersion->attach( $field, 'store' );
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
        if ( $offset === null || !$this->offsetExists( $offset ) )
            throw new \InvalidArgumentException( "FieldCollection is locked and offset:{$offset} can not be appended!" );
        $this->offsetGet( $offset )->type->value = $value;
    }

    /**
     * Unset value on a offset in collection
     *
     * @internal
     * @throws \InvalidArgumentException This collection is readonly
     * @param string|int $offset
     */
    public function offsetUnset( $offset )
    {
        throw new \InvalidArgumentException( "This collection is readonly and offset:{$offset} can not be Unset " );
    }
}

?>
