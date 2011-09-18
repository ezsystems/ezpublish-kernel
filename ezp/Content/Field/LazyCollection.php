<?php
/**
 * File contains Field Collection class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Field;
use ezp\Base\Collection\Lazy,
    ezp\Content\Service as ContentService,
    ezp\Content\Version,
    ezp\Content\FieldType\Factory as FieldTypeFactory,
    ezp\Base\Exception\Logic as LogicException,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Content\FieldType\Value as FieldValue;

/**
 * Field Collection class. Fields are indexed by field identifier
 * This collection uses lazy loading mechanism.
 */
class LazyCollection extends Lazy
{
    /**
     * Constructor
     *
     * @param \ezp\Content\Service $contentService Content service to be used for fetching versions
     * @param \ezp\Content/Version $version Version this fields collection belongs to.
     */
    public function __construct( ContentService $contentService, Version $version )
    {
        parent::__construct( 'ezp\\Content\\Field', $contentService, $version, 'loadFields' );
    }

    /**
     * Tries to assign $value as field value to a Field object identified by $identifier
     *
     * @param string $identifier Field identifier
     * @param \ezp\Content\FieldType\Value $value Field value object
     * @throws \ezp\Base\Exception\Logic If any field identified by $identifier doesn't exist in the collection
     * @throws \ezp\Base\Exception\InvalidArgumentType If $value is not a field value object
     * @todo Direct string assignation ? If we decide to implement this, magic should be done here
     */
    public function offsetSet( $identifier, $value )
    {
        $this->load();
        if ( !$this->offsetExists( $identifier ) )
            throw new LogicException( 'FieldCollection', "Field with identifier '$identifier' doesn't exist in this collection" );

        $field = $this->offsetGet( $identifier );
        if ( !$value instanceof FieldValue )
        {
            if ( !is_scalar( $value ) )
                throw new InvalidArgumentType( 'value', 'ezp\\Content\\FieldType\\Value or scalar', $value );

            $value = FieldTypeFactory::buildValueFromString(
                $field->fieldDefinition->fieldType,
                (string)$value
            );
        }

        $field->value = $value;
    }
}
