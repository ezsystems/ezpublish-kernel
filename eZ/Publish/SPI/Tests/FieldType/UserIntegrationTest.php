<?php
/**
 * File contains: eZ\Publish\SPI\Tests\FieldType\UserIntegrationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\Persistence\Legacy;
use eZ\Publish\Core\FieldType;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\SPI\Persistence\User;

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
class UserIntegrationTest extends BaseIntegrationTest
{
    /**
     * Get name of tested field type
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezuser';
    }

    /**
     * Get handler with required custom field types registered
     *
     * @return Handler
     */
    public function getCustomHandler()
    {
        $handler = $this->getHandler();

        $handler->getFieldTypeRegistry()->register(
            'ezuser',
            new FieldType\User\Type()
        );
        $handler->getStorageRegistry()->register(
            'ezuser',
            new FieldType\User\UserStorage(
                array(
                    'LegacyStorage' => new FieldType\User\UserStorage\Gateway\LegacyStorage(),
                )
            )
        );
        $handler->getFieldValueConverterRegistry()->register(
            'ezuser',
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
        return new FieldTypeConstraints();
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
            // The user field type does not have any special field definition
            // properties
            array( 'fieldType', 'ezuser' ),
            array( 'fieldTypeConstraints', new Content\FieldTypeConstraints() ),
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
                'data'         => null,
                'externalData' => array(),
                'sortKey'      => 'user',
            )
        );
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
        $expectedValues = array(
            'hasStoredLogin' => true,
            'contentId' => self::$contentId,
            'login' => 'hans',
            'email' => 'hans@example.com',
            'passwordHash' => '*',
            'passwordHashType' => 0,
            'enabled' => true,
            'maxLogin' => 1000,
        );

        foreach ( $expectedValues as $key => $value )
        {
            $this->assertEquals( $value, $field->value->externalData[$key] );
        }
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
                'data'         => null,
                'externalData' => array(
                    'login'            => 'change', // Change is intended to not get through
                    'email'            => 'change', // Change is intended to not get through
                    'passwordHash'     => 'change', // Change is intended to not get through
                    'passwordHashType' => 'change', // Change is intended to not get through
                    'enabled'          => 'changed', // Change is intended to not get through
                    'maxLogin'         => 'changed', // Change is intended to not get through
                ),
                'sortKey'      => 'user',
            )
        );
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
        // No update of user data possible through field type
        $this->assertLoadedFieldDataCorrect( $field );
    }

    /**
     * Method called after content creation
     *
     * Useful, if additional stuff should be executed (like creating the actual
     * user).
     *
     * @param Legacy\Handler $handler
     * @param Content $content
     *
     * @return void
     */
    public function postCreationHook( Legacy\Handler $handler, Content $content )
    {
        $user = new User();
        $user->id            = $content->versionInfo->contentInfo->id;
        $user->login         = 'hans';
        $user->email         = 'hans@example.com';
        $user->passwordHash  = '*';
        $user->hashAlgorithm = 0;
        $user->isEnabled     = true;
        $user->maxLogin      = 1000;

        $userHandler = $handler->userHandler();
        $userHandler->create( $user );
    }
}

