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
    eZ\Publish\Core\FieldType\User\Value as UserValue;

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
        return new UserValue( array(
            'accountKey' => null,
            'isEnabled'  => true,
            'lastVisit'  => null,
            'loginCount' => 0,
            'maxLogin'   => 1000,
        ) );
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
            array( 'accountKey', null ),
            array( 'hasStoredLogin', true ),
            array( 'contentobjectId', 226 ),
            array( 'login', 'hans' ),
            array( 'email', 'hans@example.com' ),
            array( 'passwordHash', '680869a9873105e365d39a6d14e68e46' ),
            array( 'passwordHashType', 2 ),
            array( 'isLoggedIn', true ),
            array( 'isEnabled', true ),
            // @TODO: Fails because of maxLogin problem
            array( 'isLocked', false ),
            array( 'lastVisit', null ),
            array( 'loginCount', null ),
            // @TODO: Currently not editable through UserService, tests will
            // fail
            array( 'maxLogin', 1000 ),
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
            'accountKey'        => 'foobar',
            'login'              => 'change', // Change is intended to not get through
            'email'              => 'change', // Change is intended to not get through
            'passwordHash'      => 'change', // Change is intended to not get through
            'passwordHashType' => 'change', // Change is intended to not get through
            'lastVisit'         => 123456789,
            'loginCount'        => 2300,
            'isEnabled'         => 'changed', // Change is intended to not get through
            'maxLogin'          => 'changed', // Change is intended to not get through
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
            array( 'accountKey', 'foobar' ),
            array( 'hasStoredLogin', true ),
            array( 'contentobjectId', 226 ),
            array( 'login', 'hans' ),
            array( 'email', 'hans@example.com' ),
            array( 'passwordHash', '680869a9873105e365d39a6d14e68e46' ),
            array( 'passwordHashType', 2 ),
            array( 'isLoggedIn', true ),
            array( 'isEnabled', true ),
            // @TODO: Fails because of maxLogin problem
            array( 'isLocked', true ),
            array( 'lastVisit', 123456789 ),
            array( 'loginCount', 2300 ),
            // @TODO: Currently not editable through UserService, tests will
            // fail
            array( 'maxLogin', 1000 ),
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
            array( 'accountKey', null ),
            array( 'hasStoredLogin', false ),
            array( 'contentobjectId', null ),
            array( 'login', null ),
            array( 'email', null ),
            array( 'passwordHash', null ),
            array( 'passwordHashType', null ),
            array( 'isLoggedIn', true ),
            array( 'isEnabled', false ),
            array( 'isLocked', false ),
            array( 'lastVisit', null ),
            array( 'loginCount', null ),
            array( 'maxLogin', null ),
        );
    }

    /**
     * Get expectation for the toHash call on our field value
     *
     * @return mixed
     */
    public function getToHashExpectation()
    {
        return 'toBeDefined';
    }

    /**
     * Get hashes and their respective converted values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getHashes()
    {
        return array(
            array( 'toBeDefined', array() ),
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

