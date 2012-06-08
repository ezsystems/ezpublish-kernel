<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\HandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests;
use eZ\Publish\Core\Persistence\Legacy;

/**
 * Integration test for the legacy storage
 *
 * @group integration
 */
class UserFieldTypeIntergrationTest extends FieldTypeIntegrationTest
{
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
     * Get initial field externals data
     *
     * @return array
     */
    public function getInitialFieldData()
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

    public function testRemoveAccountKey()
    {
        $handler = $this->getCustomHandler();

        $contentHandler = $handler->contentHandler();
        $content = $contentHandler->load( 10, 2 );
        $field = $content->fields[2];
        $field->value->externalData['account_key'] = null;

        $updateStruct = new \eZ\Publish\SPI\Persistence\Content\UpdateStruct( array(
            'creatorId' => 14,
            'modificationDate' => time(),
            'initialLanguageId' => 2,
            'fields' => array(
                $field,
            )
        ) );

        $contentHandler = $handler->contentHandler();
        $content = $contentHandler->updateContent( 10, 2, $updateStruct );

        $this->assertNull(
            $content->fields[2]->value->externalData['account_key']
        );
    }
}

