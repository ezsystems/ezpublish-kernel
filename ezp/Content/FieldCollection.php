<?php
/**
 * File contains Field Collection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

/**
 * Field Collection class
 *
 * Readonly class that takes (Content) Version as input.
 *
 */
namespace ezp\Content;
class FieldCollection extends \ezp\Base\ReadOnlyCollection
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
}

?>
