<?php
/**
 * File contains Field Collection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Field;
use ezp\Base\Configuration,
    ezp\Base\Exception\BadConfiguration,
    ezp\Base\Exception\MissingClass,
    ezp\Base\Exception\ReadOnly as ReadOnlyException,
    ezp\Base\Collection\ReadOnly,
    ezp\Content\Field,
    ezp\Content\Version,
    RuntimeException;

/**
 * Field Collection class
 *
 * Readonly class that takes (Content) Version as input.
 *
 */
class Collection extends ReadOnly
{
    /**
     * Constructor, sets up Collection based on contentType fields
     *
     * @param Version $contentVersion
     */
    public function __construct( Version $contentVersion )
    {
        $elements = array();
        $fieldTypes = Configuration::getInstance( 'content' )->get( 'fields', 'Type' );
        foreach ( $contentVersion->content->contentType->fields as $fieldDefinition )
        {
            if ( !isset( $fieldTypes[$fieldDefinition->fieldTypeString] ) )
                throw new BadConfiguration( 'content.ini[fields]', "could not load {$fieldDefinition->fieldTypeString}" );

            if ( !class_exists( $fieldTypes[$fieldDefinition->fieldTypeString] ) )
                throw new MissingClass(  $fieldTypes[$fieldDefinition->fieldTypeString], 'field type' );

            $className = $fieldTypes[$fieldDefinition->fieldTypeString];
            $elements[ $fieldDefinition->identifier ] = $field = new $className( $contentVersion, $fieldDefinition );

            if ( !$field instanceof Field )
                throw new RuntimeException( "Field type value '{$className}' does not implement ezp\\content\\Field" );

            $contentVersion->attach( $field, 'store' );
        }
        parent::__construct( $elements );
    }

    /**
     * Set value on a offset in collection, only allowed on existing items where value is forwarded to ->type->value
     *
     * @internal
     * @throws ezp\Base\Exception\ReadOnly When trying to set new values / append
     * @param string|int $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        if ( $offset === null || !$this->offsetExists( $offset ) )
            throw new ReadOnlyException( "Field\\Collection" );
        $this->offsetGet( $offset )->value = $value;
    }
}

?>
