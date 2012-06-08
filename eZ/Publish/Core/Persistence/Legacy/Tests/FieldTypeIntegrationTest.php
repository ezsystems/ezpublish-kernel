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
abstract class FieldTypeIntegrationTest extends TestCase
{
    /**
     * Property indicating wether the DB already has been set up
     *
     * @var bool
     */
    protected static $setUp = false;

    /**
     * Get handler with required custom field types registered
     *
     * @return Handler
     */
    abstract public function getCustomHandler();

    /**
     * Get initial field externals data
     *
     * @return array
     */
    abstract public function getInitialFieldData();

    /**
     * Get externals field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    abstract public function getExternalsFieldData();

    /**
     * Get update field externals data
     *
     * @return array
     */
    abstract public function getUpdateFieldData();

    /**
     * Get externals updated field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    abstract public function getUpdatedExternalsFieldData();

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
    public function testLoadField()
    {
        $handler = $this->getCustomHandler();

        $contentHandler = $handler->contentHandler();
        return $contentHandler->load( 10, 2 );
    }

    /**
     * @depends testLoadField
     */
    public function testLoadFieldType( $content )
    {
        $this->assertSame(
            'ezuser',
            $content->fields[2]->type
        );

        return $content->fields[2];
    }

    /**
     * @depends testLoadFieldType
     * @dataProvider getExternalsFieldData
     */
    public function testLoadExternalData( $name, $value, $field )
    {
        if ( !array_key_exists( $name, $field->value->externalData ) )
        {
            $this->fail( "Property $name not avialable." );
        }

        $this->assertEquals(
            $value,
            $field->value->externalData[$name]
        );
    }

    /**
     * @depends testLoadFieldType
     */
    public function testUpdateField( $field )
    {
        $handler = $this->getCustomHandler();

        $field->value->externalData = $this->getUpdateFieldData();
        $updateStruct = new \eZ\Publish\SPI\Persistence\Content\UpdateStruct( array(
            'creatorId' => 14,
            'modificationDate' => time(),
            'initialLanguageId' => 2,
            'fields' => array(
                $field,
            )
        ) );

        $contentHandler = $handler->contentHandler();
        return $contentHandler->updateContent( 10, 2, $updateStruct );
    }

    /**
     * @depends testUpdateField
     */
    public function testUpdateFieldType( $content )
    {
        $this->assertSame(
            'ezuser',
            $content->fields[2]->type
        );

        return $content->fields[2];
    }

    /**
     * @depends testUpdateFieldType
     * @dataProvider getUpdatedExternalsFieldData
     */
    public function testUpdateExternalData( $name, $value, $field )
    {
        if ( !array_key_exists( $name, $field->value->externalData ) )
        {
            $this->fail( "Property $name not avialable." );
        }

        $this->assertEquals(
            $value,
            $field->value->externalData[$name]
        );
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
        return new \PHPUnit_Framework_TestSuite( get_called_class() );
    }
}
