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
class KeywordIntergrationTest extends BaseIntegrationTest
{
    /**
     * Get name of tested field tyoe
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezkeyword';
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
            'ezkeyword',
            new FieldType\Keyword\KeywordStorage(
                $handler,
                array(
                    'LegacyStorage' => new FieldType\Keyword\KeywordStorage\Gateway\LegacyStorage(),
                )
            )
        );
        $handler->getFieldValueConverterRegistry()->register(
            'ezkeyword',
            new Legacy\Content\FieldValue\Converter\Null()
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
            // The ezkeyword field type does not have any special field definition
            // properties
            array( 'fieldType', 'ezkeyword' ),
            array( 'fieldTypeConstraints', new Content\FieldTypeConstraints() ),
        );
    }

    /**
     * Get initial field externals data
     *
     * @return array
     */
    public function getInitialFieldData()
    {
        return array( 'foo', 'bar', 'sindelfingen' );
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
        $this->assertKeywordSetsEqual(
            $this->getInitialFieldData(),
            $field->value->externalData
        );
    }

    /**
     * Asserts that 2 keyword sets equal
     *
     * @param array $expectedKeywords
     * @param array $actualKeywords
     * @return void
     */
    protected function assertKeywordSetsEqual( $expectedKeywords, $actualKeywords )
    {
        // Assert all expected keywords are loaded
        foreach ( $expectedKeywords as $keyword )
        {
            if ( ( $index = array_search( $keyword, $actualKeywords ) ) === false )
            {
                $this->fail(
                    sprintf(
                        'Keyword "%s" not loaded.',
                        $keyword
                    )
                );
            }
            unset( $actualKeywords[$index] );
        }

        // Assert no additional keywords have been loaded
        if ( count( $actualKeywords ) !== 0 )
        {
            $this->fail(
                sprintf(
                    'Loaded unexpected keywords: "%s"',
                    implode( '", "', $actualKeywords )
                )
            );
        }
    }

    /**
     * Get update field externals data
     *
     * @return array
     */
    public function getUpdateFieldData()
    {
        return array( 'sindelfingen', 'baz' );
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
        $this->assertKeywordSetsEqual(
            $this->getUpdateFieldData(),
            $field->value->externalData
        );
    }
}

