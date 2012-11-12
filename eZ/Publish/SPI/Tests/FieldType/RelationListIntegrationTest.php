<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\HandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Tests\FieldType;
use eZ\Publish\Core\Persistence\Legacy,
    eZ\Publish\Core\FieldType,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;

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
class RelationListIntegrationTest extends BaseIntegrationTest
{
    /**
     * Get name of tested field tyoe
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezobjectrelationlist';
    }

    /**
     * Get handler with required custom field types registered
     *
     * @return Handler
     */
    public function getCustomHandler()
    {
        $handler = $this->getHandler();

        $handler->getStorageRegistry()->register(
            'ezobjectrelationlist',
            new FieldType\Relation\RelationStorage(
                array(
                    'LegacyStorage' => new FieldType\RelationList\RelationListStorage\Gateway\LegacyStorage(),
                )
            )
        );
        $handler->getFieldValueConverterRegistry()->register(
            'ezobjectrelationlist',
            new Legacy\Content\FieldValue\Converter\RelationList( $this->handler )
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
        return new Content\FieldTypeConstraints(
            array( 'fieldSettings' => array(
                'selectionMethod' => 0,
                'selectionDefaultLocation' => '',
                'selectionContentTypes' => array(),
            )
        ) );
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
        $fieldSettings = array(
            'selectionMethod' => 0,
            'selectionDefaultLocation' => '',
            'selectionContentTypes' => array(),
        );

        return array(
            array( 'fieldType', 'ezobjectrelationlist' ),
            array( 'fieldTypeConstraints', new Content\FieldTypeConstraints( array( 'fieldSettings' => $fieldSettings ) ) ),
        );
    }

    /**
     * Get initial field value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getInitialValue()
    {
        return new Content\FieldValue( array(
            'data'         => array( 'destinationContentIds' => array( 4 ) ),
            'externalData' => array( 'destinationContentIds' => array( 4 ) ),
            'sortKey'      => null,
        ) );
    }

    /**
     * Asserts that the loaded field data is correct
     *
     * Performs assertions on the loaded field, mainly checking that the
     * $field->value->externalData is loaded correctly. If the loading of
     * external data manipulates other aspects of $field, their correctness
     * also needs to be asserted. Make sure you implement this method agnostic
     * to the used SPI\Persistence implementation!
     */
    public function assertLoadedFieldDataCorrect( Field $field )
    {
        $expected = $this->getInitialValue();
        $this->assertEquals(
            $expected->externalData,
            $field->value->externalData
        );

        $this->assertNotNull(
            $field->value->data['destinationContentIds']
        );
        $this->assertEquals(
            $expected->data['destinationContentIds'],
            $field->value->data['destinationContentIds']
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
        return new Content\FieldValue( array(
            'data'         => array( 'destinationContentIds' => array( 11 ) ),
            'externalData' => array( 'destinationContentIds' => array( 11 ) ),
            'sortKey'      => null,
        ) );
    }

    /**
     * Asserts that the updated field data is loaded correct
     *
     * Performs assertions on the loaded field after it has been updated,
     * mainly checking that the $field->value->externalData is loaded
     * correctly. If the loading of external data manipulates other aspects of
     * $field, their correctness also needs to be asserted. Make sure you
     * implement this method agnostic to the used SPI\Persistence
     * implementation!
     *
     * @return void
     */
    public function assertUpdatedFieldDataCorrect( Field $field )
    {
        $expected = $this->getUpdatedValue();
        $this->assertEquals(
            $expected->externalData,
            $field->value->externalData
        );

        $this->assertNotNull(
            $field->value->data['destinationContentIds']
        );
        $this->assertEquals(
            $expected->data['destinationContentIds'],
            $field->value->data['destinationContentIds']
        );
    }
}

