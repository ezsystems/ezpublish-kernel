<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\RepositoryTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;
use eZ\Publish\API\Repository,
    eZ\Publish\Core\FieldType\User\Value as UserValue,
    eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Integration test for use field type
 *
 * @group integration
 * @group field-type
 */
class UserFieldTypeIntergrationTest extends BaseIntegrationTest
{
    /**
     * Identifier of the custom field
     *
     * @var string
     */
    protected $customFieldIdentifier = "user_account";

    /**
     * Get name of tested field tyoe
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezuser';
    }

    /**
     * Get expected settings schema
     *
     * @return array
     */
    public function getSettingsSchema()
    {
        return array();
    }

    /**
     * Get a valid $fieldSettings value
     *
     * @return mixed
     */
    public function getValidFieldSettings()
    {
        return array();
    }

    /**
     * Get $fieldSettings value not accepted by the field type
     *
     * @return mixed
     */
    public function getInvalidFieldSettings()
    {
        return array(
            'somethingUnknown' => 0,
        );
    }

    /**
     * Get expected validator schema
     *
     * @return array
     */
    public function getValidatorSchema()
    {
        return array();
    }

    /**
     * Get a valid $validatorConfiguration
     *
     * @return mixed
     */
    public function getValidValidatorConfiguration()
    {
        return array();
    }

    /**
     * Get $validatorConfiguration not accepted by the field type
     *
     * @return mixed
     */
    public function getInvalidValidatorConfiguration()
    {
        return array(
            'unkknown' => array( 'value' => 23 )
        );
    }

    /**
     * Get initial field externals data
     *
     * @return array
     */
    public function getValidCreationFieldData()
    {
        return new UserValue( array(
            'accountKey' => null,
            'isEnabled'  => true,
            'lastVisit'  => null,
            'loginCount' => 0,
            'maxLogin'   => 1000,
        ) );
    }

    /**
     * Asserts that the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()}
     * was stored and loaded correctly.
     *
     * @param Field $field
     * @return void
     */
    public function assertFieldDataLoadedCorrect( Field $field )
    {
        $this->assertInstanceOf(
            'eZ\Publish\Core\FieldType\User\Value',
            $field->value
        );

        $expectedData = array(
            'accountKey' => null,
            'hasStoredLogin' => true,
            'contentobjectId' => 226,
            'login' => 'hans',
            'email' => 'hans@example.com',
            'passwordHash' => '680869a9873105e365d39a6d14e68e46',
            'passwordHashType' => 2,
            'isLoggedIn' => true,
            'isEnabled' => true,
            // @TODO: Fails because of maxLogin problem
            'isLocked' => false,
            'lastVisit' => null,
            'loginCount' => null,
            // @TODO: Currently not editable through UserService, tests will
            // fail
            'maxLogin' => 1000,
        );

        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );
    }

    /**
     * Get field data which will result in errors during creation
     *
     * This is a PHPUnit data provider.
     *
     * The returned records must contain of an error producing data value and
     * the expected exception class (from the API or SPI, not implementation
     * specific!) as the second element. For example:
     *
     * <code>
     * array(
     *      array(
     *          new DoomedValue( true ),
     *          'eZ\\Publish\\API\\Repository\\Exceptions\\ContentValidationException'
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array[]
     */
    public function provideInvalidCreationFieldData()
    {
        return array();
    }

    public function testCreateContentFails( $failingValue = null, $expectedException = null )
    {
        $this->markTestSkipped( "Values are ignored on creation." );
    }

    /**
     * Get update field externals data
     *
     * @return array
     */
    public function getValidUpdateFieldData()
    {
        return new UserValue( array(
            'accountKey'       => 'foobar',
            'login'            => 'change', // Change is intended to not get through
            'email'            => 'change', // Change is intended to not get through
            'passwordHash'     => 'change', // Change is intended to not get through
            'passwordHashType' => 'change', // Change is intended to not get through
            'lastVisit'        => 123456789,
            'loginCount'       => 2300,
            'isEnabled'        => 'changed', // Change is intended to not get through
            'maxLogin'         => 'changed', // Change is intended to not get through
        ) );
    }

    /**
     * Get externals updated field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function assertUpdatedFieldDataLoadedCorrect( Field $field )
    {
        $this->assertInstanceOf(
            'eZ\Publish\Core\FieldType\User\Value',
            $field->value
        );

        $expectedData = array(
            'accountKey' => 'foobar',
            'hasStoredLogin' => true,
            'contentobjectId' => 226,
            'login' => 'hans',
            'email' => 'hans@example.com',
            'passwordHash' => '680869a9873105e365d39a6d14e68e46',
            'passwordHashType' => 2,
            'isLoggedIn' => true,
            'isEnabled' => true,
            // @TODO: Fails because of maxLogin problem
            'isLocked' => true,
            'lastVisit' => 123456789,
            'loginCount' => 2300,
            // @TODO: Currently not editable through UserService, tests will
            // fail
            'maxLogin' => 1000,
        );

        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );
    }

    /**
     * Get field data which will result in errors during update
     *
     * This is a PHPUnit data provider.
     *
     * The returned records must contain of an error producing data value and
     * the expected exception class (from the API or SPI, not implementation
     * specific!) as the second element. For example:
     *
     * <code>
     * array(
     *      array(
     *          new DoomedValue( true ),
     *          'eZ\\Publish\\API\\Repository\\Exceptions\\ContentValidationException'
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array[]
     */
    public function provideInvalidUpdateFieldData()
    {
        return array(
            array(
                null,
                'eZ\\Publish\\API\\Repository\\Exceptions\\ContentValidationException'
            ),
            // TODO: Define more failure cases ...
        );
    }

    /**
     * Asserts the the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()};
     * was copied and loaded correctly.
     *
     * @param Field $field
     */
    public function assertCopiedFieldDataLoadedCorrectly( Field $field )
    {
        $this->assertInstanceOf(
            'eZ\Publish\Core\FieldType\User\Value',
            $field->value
        );

        $expectedData = array(
            'accountKey' => null,
            'hasStoredLogin' => false,
            'contentobjectId' => null,
            'login' => null,
            'email' => null,
            'passwordHash' => null,
            'passwordHashType' => null,
            'isLoggedIn' => true,
            'isEnabled' => false,
            'isLocked' => false,
            'lastVisit' => null,
            'loginCount' => null,
            'maxLogin' => null,
        );

        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );
        return ;
    }

    /**
     * Get data to test to hash method
     *
     * This is a PHPUnit data provider
     *
     * The returned records must have the the original value assigned to the
     * first index and the expected hash result to the second. For example:
     *
     * <code>
     * array(
     *      array(
     *          new MyValue( true ),
     *          array( 'myValue' => true ),
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array
     */
    public function provideToHashData()
    {
        return array(
            array( new UserValue(), 'toBeDefined' )
        );
    }

    /**
     * Get hashes and their respective converted values
     *
     * This is a PHPUnit data provider
     *
     * The returned records must have the the input hash assigned to the
     * first index and the expected value result to the second. For example:
     *
     * <code>
     * array(
     *      array(
     *          array( 'myValue' => true ),
     *          new MyValue( true ),
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array
     */
    public function provideFromHashData()
    {
        return array(
            array( 'toBeDefined', array() ),
        );
    }

    /**
     * Overwrite normal content creation
     *
     * @param mixed $fieldData
     * @return void
     */
    protected function createContent( $fieldData )
    {
        $repository  = $this->getRepository();
        $userService = $repository->getUserService();

        // Instantiate a create struct with mandatory properties
        $userCreate = $userService->newUserCreateStruct(
            'hans',
            'hans@example.com',
            'password',
            'eng-US'
        );
        $userCreate->enabled  = true;

        // Set some fields required by the user ContentType
        $userCreate->setField( 'first_name', 'Example' );
        $userCreate->setField( 'last_name', 'User' );

        // ID of the "Editors" user group in an eZ Publish demo installation
        $group = $userService->loadUserGroup( 13 );

        // Create a new user instance.
        $user = $userService->createUser( $userCreate, array( $group ) );

        // Create draft from user content object
        $contentService = $repository->getContentService();
        return $contentService->createContentDraft( $user->content->contentInfo, $user->content->versionInfo );
    }
}

