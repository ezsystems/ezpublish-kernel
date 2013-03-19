<?php
/**
 * File contains TimeIntegrationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\Persistence\Legacy;
use eZ\Publish\Core\FieldType;
use eZ\Publish\SPI\Persistence\Content;

/**
 * Integration test for legacy storage field types
 *
 * This abstract base test case is supposed to be the base for field type
 * integration tests. It basically calls all involved methods in the field type
 * ``Converter`` and ``Storage`` implementations. Fo get it working implement
 * the abstract methods in a sensible way.
 *
 * The following actions are performed by this test using the custom field
 * type:
 *
 * - Create a new content type with the given field type
 * - Load create content type
 * - Create content object of new content type
 * - Load created content
 * - Copy created content
 * - Remove copied content
 *
 * @group integration
 */
class TimeIntegrationTest extends BaseIntegrationTest
{
    /**
     * Get name of tested field type
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'eztime';
    }

    /**
     * Get handler with required custom field types registered
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Handler
     */
    public function getCustomHandler()
    {
        $handler = $this->getHandler();

        $handler->getFieldTypeRegistry()->register(
            'eztime',
            new FieldType\Time\Type()
        );
        $handler->getStorageRegistry()->register(
            'eztime',
            new FieldType\NullStorage()
        );
        $handler->getFieldValueConverterRegistry()->register(
            'eztime',
            new Legacy\Content\FieldValue\Converter\Time()
        );

        return $handler;
    }

    /**
     * Returns the FieldTypeConstraints to be used to create a field definition
     * of the FieldType under test.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints
     */
    public function getTypeConstraints()
    {
        return new Content\FieldTypeConstraints();
    }

    /**
     * Get field definition data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getFieldDefinitionData()
    {
        return array(
            // The eztime field type does not have any special field definition
            // properties
            array( 'fieldType', 'eztime' ),
            array(
                'fieldTypeConstraints',
                new Content\FieldTypeConstraints(
                    array(
                        'fieldSettings' => new FieldType\FieldSettings(
                            array(
                                'defaultType'  => 0,
                                'useSeconds'   => false
                            )
                        ),
                    )
                )
            ),
        );
    }

    /**
     * Get initial field value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getInitialValue()
    {
        return new Content\FieldValue(
            array(
                'data' => 3661,
                'externalData' => null,
                'sortKey' => 42,
            )
        );
    }

    /**
     * Get update field value.
     *
     * Use to update the field
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getUpdatedValue()
    {
        return new Content\FieldValue(
            array(
                'data' => 7322,
                'externalData' => null,
                'sortKey' => 23,
            )
        );
    }
}
