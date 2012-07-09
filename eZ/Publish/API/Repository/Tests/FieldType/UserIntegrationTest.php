<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\RepositoryTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;
use eZ\Publish\API\Repository;

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
            // properties, so there is nothing to check for
            array( 'fieldTypeIdentifier', 'ezuser' ),
            array( 'fieldSettings', null ),
            array( 'validatorConfiguration', null ),
        );
    }

    /**
     * Get initial field externals data
     *
     * @return array
     */
    public function getInitialFieldData()
    {
        return array(
            'account_key' => null,
            'is_enabled'  => true,
            'last_visit'  => null,
            'login_count' => 0,
            'max_login'   => 1000,
        );
    }

    /**
     * Get externals field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getExternalsFieldData()
    {
        return array(
            array( 'account_key', null ),
            array( 'has_stored_login', true ),
            array( 'contentobject_id', 226 ),
            array( 'login', 'hans' ),
            array( 'email', 'hans@example.com' ),
            array( 'password_hash', '680869a9873105e365d39a6d14e68e46' ),
            array( 'password_hash_type', 2 ),
            array( 'is_logged_in', true ),
            array( 'is_enabled', true ),
            // @TODO: Fails because of max_login problem
            array( 'is_locked', false ),
            array( 'last_visit', null ),
            array( 'login_count', null ),
            // @TODO: Currently not editable through UserService, tests will
            // fail
            array( 'max_login', 1000 ),
        );
    }

    /**
     * Get update field externals data
     *
     * @return array
     */
    public function getUpdateFieldData()
    {
        return array(
            'account_key'        => 'foobar',
            'login'              => 'change', // Change is intended to not get through
            'email'              => 'change', // Change is intended to not get through
            'password_hash'      => 'change', // Change is intended to not get through
            'password_hash_type' => 'change', // Change is intended to not get through
            'last_visit'         => 123456789,
            'login_count'        => 2300,
            'is_enabled'         => 'changed', // Change is intended to not get through
            'max_login'          => 'changed', // Change is intended to not get through
        );
    }

    /**
     * Get externals updated field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getUpdatedExternalsFieldData()
    {
        return array(
            array( 'account_key', 'foobar' ),
            array( 'has_stored_login', true ),
            array( 'contentobject_id', 226 ),
            array( 'login', 'hans' ),
            array( 'email', 'hans@example.com' ),
            array( 'password_hash', '680869a9873105e365d39a6d14e68e46' ),
            array( 'password_hash_type', 2 ),
            array( 'is_logged_in', true ),
            array( 'is_enabled', true ),
            // @TODO: Fails because of max_login problem
            array( 'is_locked', true ),
            array( 'last_visit', 123456789 ),
            array( 'login_count', 2300 ),
            // @TODO: Currently not editable through UserService, tests will
            // fail
            array( 'max_login', 1000 ),
        );
    }

    /**
     * Get externals copied field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getCopiedExternalsFieldData()
    {
        return array(
            array( 'account_key', null ),
            array( 'has_stored_login', false ),
            array( 'contentobject_id', null ),
            array( 'login', null ),
            array( 'email', null ),
            array( 'password_hash', null ),
            array( 'password_hash_type', null ),
            array( 'is_logged_in', true ),
            array( 'is_enabled', false ),
            array( 'is_locked', false ),
            array( 'last_visit', null ),
            array( 'login_count', null ),
            array( 'max_login', null ),
        );
    }

    public function createContentOverwrite()
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

        return $user->content;
    }
}

