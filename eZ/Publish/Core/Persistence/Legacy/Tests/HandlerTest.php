<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\HandlerTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests;

use eZ\Publish\Core\Base\ConfigurationManager;
use eZ\Publish\Core\Base\ServiceContainer;
use eZ\Publish\Core\Persistence\Legacy\Handler;
use PDOException;

/**
 * Test case for Repository Handler
 */
class HandlerTest extends TestCase
{
    /**
     * Does not reset database for this class as this class only tests handler instances.
     *
     * @return void
     */
    public function setUp()
    {
        try
        {
            $this->getDatabaseHandler();
        }
        catch ( PDOException $e )
        {
            $this->fail(
                'PDO session could not be created: ' . $e->getMessage()
            );
        }
    }

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
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::transactionHandler
     *
     * @return void
     */
    public function testTransactionHandler()
    {
        $handler = $this->getHandlerFixture();
        $transactionHandler = $handler->transactionHandler();

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\TransactionHandler',
            $transactionHandler
        );
        $this->assertInstanceOf(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\TransactionHandler',
            $transactionHandler
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Handler::transactionHandler
     *
     * @return void
     */
    public function testTransactionHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->transactionHandler(),
            $handler->transactionHandler()
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
     * @covers \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler::createFromDSN
     *
     * @return void
     */
    public function testDatabaseInstance()
    {
        $method = new \ReflectionProperty(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\TransactionHandler',
            'dbHandler'
        );
        $method->setAccessible( true );

        $dbHandler = $method->getValue( $this->getHandlerFixture()->transactionHandler() );
        $className = get_class( $this->getDatabaseHandler() );

        $this->assertTrue( $dbHandler instanceof $className, get_class( $dbHandler ) . " not of type $className." );
    }
}
