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
class IntegrationTest extends TestCase
{
    protected static $setUp = false;

    /**
     * Only set up once for these read only tests on a large fixture
     *
     * Skipping the reset-up, since setting up for these tests takes quite some
     * time, which is not required to spent, since we are only reading from the
     * database anyways.
     *
     * @return void
     */
    public function setUp()
    {
        if ( !self::$setUp )
        {
            parent::setUp();
            $this->insertDatabaseFixture( __DIR__ . '/Content/SearchHandler/_fixtures/full_dump.php' );
            self::$setUp = $this->handler;
        }
        else
        {
            $this->handler = self::$setUp;
        }
    }

    /**
     * @return void
     */
    public function testLoadUserUserField()
    {
        $handler        = $this->getHandler();

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

        $contentHandler = $handler->contentHandler();
        return $contentHandler->load( 10, 2 );
    }

    /**
     * @depends testLoadUserUserField
     */
    public function testLoadUserUserFieldType( $content )
    {
        $this->assertSame(
            'ezuser',
            $content->fields[2]->type
        );

        return $content->fields[2];
    }

    /**
     * @depends testLoadUserUserFieldType
     */
    public function testLoadUserUserExternalData( $field )
    {
        $this->assertEquals(
            array(
                'account_key'      => null,
                'has_stored_login' => true,
                'is_logged_in'     => true,
                'is_enabled'       => true,
                'is_locked'        => false,
                'last_visit'       => null,
                'login_count'      => null,
                'max_login'        => 1000,
            ),
            $field->value->externalData
        );
    }

    /**
     * @return void
     */
    public function testLoadUserImageField()
    {
        $this->markTestSkipped( "@TODO: We will work on this later…" );

        $handler        = $this->getHandler();

        $handler->getStorageRegistry()->register(
            'ezimage',
            new Legacy\Content\FieldValue\Converter\ImageStorage()
        );
        $handler->getFieldValueConverterRegistry()->register(
            'ezimage',
            new Legacy\Content\FieldValue\Converter\Image()
        );

        $contentHandler = $handler->contentHandler();
        $contentHandler->load( 10, 2 );
    }

    /**
     * Returns the Handler
     *
     * @return Handler
     */
    protected function getHandler()
    {
        return new Legacy\Handler(
            array(
                'external_storage' => array(
                    'ezauthor' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
                    'ezstring' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
                    'ezuser'   => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
                    'eztext'   => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
                    'ezimage'  => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\NullStorage',
                ),
                'field_converter' => array(
                    'ezauthor' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\TextLine',
                    'ezstring' => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\TextLine',
                    'ezuser'   => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
                    'eztext'   => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\TextBlock',
                    'ezimage'  => 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Integer',
                )
            ),
            self::$setUp
        );
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
