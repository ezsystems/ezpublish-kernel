<?php
/**
 * File contains Field Collection class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Field;
use ezp\Base\Configuration,
    ezp\Base\Exception\BadConfiguration,
    ezp\Base\Exception\MissingClass,
    ezp\Base\Exception\Logic as LogicException,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Base\Collection\ReadOnly,
    ezp\Content\Field,
    ezp\Content\Version,
    eZ\Publish\Core\Repository\FieldType\Factory as FieldTypeFactory,
    eZ\Publish\Core\Repository\FieldType\Value as FieldValue,
    RuntimeException;

/**
 * Field Collection class
 *
 * Readonly class that takes (Content) Version as input.
 *
 */
class StaticCollection extends TypeCollection
{
    /**
     * Constructor, sets up Collection based on contentType fields
     *
     * @param Version $contentVersion
     */
    public function __construct( Version $contentVersion )
    {
        $elements = array();
        foreach ( $contentVersion->getContent()->getContentType()->getFields() as $fieldDefinition )
        {
            $elements[ $fieldDefinition->identifier ] = $field = new Field( $contentVersion, $fieldDefinition );

            // Make the Field an observer of publish & create events
            $contentVersion->attach( $field, 'pre_create' );
            $contentVersion->attach( $field, 'post_create' );

            $contentVersion->attach( $field, 'pre_publish' );
            $contentVersion->attach( $field, 'post_publish' );
        }
        parent::__construct( 'ezp\\Content\\Field', $elements );
    }

    /**
     * Tries to assign $value as field value to a Field object identified by $identifier
     *
     * @param string $identifier Field identifier
     * @param \eZ\Publish\Core\Repository\FieldType\Value $value Field value object
     * @throws \ezp\Base\Exception\Logic If any field identified by $identifier doesn't exist in the collection
     * @throws \ezp\Base\Exception\InvalidArgumentType If $value is not a field value object
     * @todo Direct string assignation ? If we decide to implement this, magic should be done here
     */
    public function offsetSet( $identifier, $value )
    {
        if ( !$this->offsetExists( $identifier ) )
            throw new LogicException( 'FieldCollection', "Field with identifier '$identifier' doesn't exist in this collection" );

        $field = $this->offsetGet( $identifier );
        if ( !$value instanceof FieldValue )
        {
            $value = FieldTypeFactory::buildValue(
                $field->fieldDefinition->fieldType,
                $value
            );
        }

        $field->setValue( $value );
    }
}
