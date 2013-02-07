<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\HandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Base\ConfigurationManager;
use eZ\Publish\Core\Base\ServiceContainer;
use eZ\Publish\Core\Persistence\Legacy\Handler;

/**
 * Test case for Repository Handler
 */
class HandlerTest extends TestCase
{
    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::contentHandler
     *
     * @return void
     */
    public function testContentHandler()
    {
        $handler = $this->getHandlerFixture();
        $contentHandler = $handler->contentHandler();

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Handler',
            $contentHandler
        );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Handler',
            $contentHandler
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::contentHandler
     *
     * @return void
     */
    public function testContentHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->contentHandler(),
            $handler->contentHandler()
        );
    }

    /**
     * Issue #97
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::contentHandler
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::getStorageRegistry
     *
     * @return void
     */
    public function testStorageRegistryReused()
    {
        $handler = $this->getHandlerFixture();

        $storageRegistry = $handler->getStorageRegistry();
        $contentHandler = $handler->contentHandler();
        $fieldHandler = $this->readAttribute(
            $contentHandler,
            'fieldHandler'
        );
        $storageHandler = $this->readAttribute(
            $fieldHandler,
            'storageHandler'
        );

        $this->assertAttributeSame(
            $storageRegistry,
            'storageRegistry',
            $storageHandler
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::getFieldValueConverterRegistry
     *
     * @return void
     */
    public function testGetFieldValueConverterRegistry()
    {
        $handler = $this->getHandlerFixture();
        $registry = $handler->getFieldValueConverterRegistry();

        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\ConverterRegistry',
            $registry
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::getFieldValueConverterRegistry
     *
     * @return void
     */
    public function testGetFieldValueConverterRegistryTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->getFieldValueConverterRegistry(),
            $handler->getFieldValueConverterRegistry()
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::getStorageRegistry
     *
     * @return void
     */
    public function testGetStorageRegistry()
    {
        $handler = $this->getHandlerFixture();
        $registry = $handler->getStorageRegistry();

        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageRegistry',
            $registry
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::getStorageRegistry
     *
     * @return void
     */
    public function testGetStorageRegistryTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->getStorageRegistry(),
            $handler->getStorageRegistry()
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::getFieldTypeRegistry
     *
     * @return void
     */
    public function testGetFieldTypeRegistry()
    {
        $handler = $this->getHandlerFixture();
        $registry = $handler->getFieldTypeRegistry();

        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\FieldTypeRegistry',
            $registry
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::getFieldTypeRegistry
     *
     * @return void
     */
    public function testGetFieldTypeRegistryTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->getFieldTypeRegistry(),
            $handler->getFieldTypeRegistry()
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::searchHandler
     *
     * @return void
     */
    public function testSearchHandler()
    {
        $handler = $this->getHandlerFixture();
        $searchHandler = $handler->searchHandler();

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Search\\Handler',
            $searchHandler
        );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Search\\Handler',
            $searchHandler
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::searchHandler
     *
     * @return void
     */
    public function testSearchHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->searchHandler(),
            $handler->searchHandler()
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::contentTypeHandler
     *
     * @return void
     */
    public function testContentTypeHandler()
    {
        $handler = $this->getHandlerFixture();
        $contentTypeHandler = $handler->contentTypeHandler();

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler',
            $contentTypeHandler
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::contentLanguageHandler
     *
     * @return void
     */
    public function testContentLanguageHandler()
    {
        $handler = $this->getHandlerFixture();
        $contentLanguageHandler = $handler->contentLanguageHandler();

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler',
            $contentLanguageHandler
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::contentTypeHandler
     *
     * @return void
     */
    public function testContentTypeHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->contentTypeHandler(),
            $handler->contentTypeHandler()
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::locationHandler
     *
     * @return void
     */
    public function testLocationHandler()
    {
        $handler = $this->getHandlerFixture();
        $locationHandler = $handler->locationHandler();

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler',
            $locationHandler
        );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Handler',
            $locationHandler
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::locationHandler
     *
     * @return void
     */
    public function testLocationHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->locationHandler(),
            $handler->locationHandler()
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::userHandler
     *
     * @return void
     */
    public function testUserHandler()
    {
        $handler = $this->getHandlerFixture();
        $userHandler = $handler->userHandler();

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\User\\Handler',
            $userHandler
        );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\User\\Handler',
            $userHandler
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::userHandler
     *
     * @return void
     */
    public function testUserHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->userHandler(),
            $handler->userHandler()
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::sectionHandler
     *
     * @return void
     */
    public function testSectionHandler()
    {
        $handler = $this->getHandlerFixture();
        $sectionHandler = $handler->sectionHandler();

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler',
            $sectionHandler
        );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Section\\Handler',
            $sectionHandler
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::sectionHandler
     *
     * @return void
     */
    public function testSectionHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->sectionHandler(),
            $handler->sectionHandler()
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::urlAliasHandler
     *
     * @return void
     */
    public function testUrlAliasHandler()
    {
        $handler = $this->getHandlerFixture();
        $urlAliasHandler = $handler->urlAliasHandler();

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler',
            $urlAliasHandler
        );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\UrlAlias\\Handler',
            $urlAliasHandler
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::urlAliasHandler
     *
     * @return void
     */
    public function testUrlAliasHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->urlAliasHandler(),
            $handler->urlAliasHandler()
        );
    }

    /**
     * Returns the Handler
     *
     * @return Handler
     */
    protected function getHandlerFixture()
    {
        // get configuration config
        if ( !( $settings = include 'config.php' ) )
        {
            throw new \RuntimeException( 'Could not find config.php, please copy config.php-DEVELOPMENT to config.php customize to your needs!' );
        }

        // load configuration uncached
        $configManager = new ConfigurationManager(
            array_merge_recursive(
                $settings,
                array(
                    'base' => array(
                        'Configuration' => array(
                            'UseCache' => false
                        )
                    )
                )
            ),
            $settings['base']['Configuration']['Paths']
        );

        $serviceSettings = $configManager->getConfiguration( 'service' )->getAll();
        $serviceSettings['legacy_db_handler']['arguments']['dsn'] = $this->getDsn();
        $sc = new ServiceContainer(
            $serviceSettings,
            array()
        );

        return $sc->get( 'persistence_handler_legacy' );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\EzcDbHandler::create
     *
     * @return void
     */
    public function testDatabaseInstance()
    {
        $method = new \ReflectionProperty(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Handler',
            'dbHandler'
        );
        $method->setAccessible( true );

        $dbHandler = $method->getValue( $this->getHandlerFixture() );
        $className = get_class( $this->getDatabaseHandler() );

        $this->assertTrue( $dbHandler instanceof $className, get_class( $dbHandler ) . " not of type $className." );
    }
}
