<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\HandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests;
use eZ\Publish\Core\Persistence\Legacy,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\User;

/**
 * Integration test for the legacy storage
 *
 * @group integration
 */
class UserFieldTypeIntergrationTest extends FieldTypeIntegrationTest
{
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
     * Get handler with required custom field types registered
     *
     * @return Handler
     */
    public function getCustomHandler()
    {
        $handler = $this->getHandler();

        $handler->getStorageRegistry()->register(
            'ezuser',
            new Legacy\Content\FieldValue\Converter\UserStorage( array(
                'LegacyStorage' => new Legacy\Content\FieldValue\Converter\UserStorage\Gateway\LegacyStorage(),
            ) )
        );
        $handler->getFieldValueConverterRegistry()->register(
            'ezuser',
            new Legacy\Content\FieldValue\Converter\User()
        );

        return $handler;
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
            // The suer field type does not have any special field definition
            // properties
            array( 'fieldType', 'ezuser' ),
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
            array( 'is_logged_in', true ),
            array( 'is_enabled', true ),
            array( 'is_locked', false ),
            array( 'last_visit', null ),
            array( 'login_count', null ),
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
            'account_key' => 'foobar',
            'is_enabled'  => false,
            'last_visit'  => 123456789,
            'login_count' => 23,
            'max_login'   => 10,
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
            array( 'is_logged_in', true ),
            array( 'is_enabled', false ),
            array( 'is_locked', true ),
            array( 'last_visit', 123456789 ),
            array( 'login_count', 23 ),
            array( 'max_login', 10 ),
        );
    }

    /**
     * Method called after content creation
     *
     * Useful, if additional stuff should be executed (like creating the actual 
     * user).
     *
     * @param Legacy\Handler $handler
     * @param Content $content
     * @return void
     */
    public function postCreationHook( Legacy\Handler $handler, Content $content )
    {
        $user = new User();
        $user->id            = $content->contentInfo->id;
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

