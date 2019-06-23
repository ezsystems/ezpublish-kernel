<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\HandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests;

use eZ\Publish\Core\Base\ServiceContainer;
use eZ\Publish\Core\Persistence\Legacy\Handler;
use eZ\Publish\Core\Persistence\Legacy\Content\Handler as ContentHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Handler as LocationHandler;
use eZ\Publish\Core\Persistence\Legacy\User\Handler as UserHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler as SectionHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler as UrlAliasHandler;
use eZ\Publish\Core\Persistence\Legacy\TransactionHandler;
use eZ\Publish\SPI\Persistence\Content\Handler as SPIContentHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as SPIContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as SPILanguageHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as SPILocationHandler;
use eZ\Publish\SPI\Persistence\User\Handler as SPIUserHandler;
use eZ\Publish\SPI\Persistence\Content\Section\Handler as SPISectionHandler;
use eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler as SPIUrlAliasHandler;
use eZ\Publish\SPI\Persistence\TransactionHandler as SPITransactionHandler;

/**
 * Test case for Repository Handler.
 */
class HandlerTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::contentHandler
     */
    public function testContentHandler()
    {
        $handler = $this->getHandlerFixture();
        $contentHandler = $handler->contentHandler();

        $this->assertInstanceOf(
            SPIContentHandler::class,
            $contentHandler
        );
        $this->assertInstanceOf(
            ContentHandler::class,
            $contentHandler
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::contentHandler
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
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::contentTypeHandler
     */
    public function testContentTypeHandler()
    {
        $handler = $this->getHandlerFixture();
        $contentTypeHandler = $handler->contentTypeHandler();

        $this->assertInstanceOf(
            SPIContentTypeHandler::class,
            $contentTypeHandler
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::contentLanguageHandler
     */
    public function testContentLanguageHandler()
    {
        $handler = $this->getHandlerFixture();
        $contentLanguageHandler = $handler->contentLanguageHandler();

        $this->assertInstanceOf(
            SPILanguageHandler::class,
            $contentLanguageHandler
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::contentTypeHandler
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
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::locationHandler
     */
    public function testLocationHandler()
    {
        $handler = $this->getHandlerFixture();
        $locationHandler = $handler->locationHandler();

        $this->assertInstanceOf(
            SPILocationHandler::class,
            $locationHandler
        );
        $this->assertInstanceOf(
            LocationHandler::class,
            $locationHandler
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::locationHandler
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
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::userHandler
     */
    public function testUserHandler()
    {
        $handler = $this->getHandlerFixture();
        $userHandler = $handler->userHandler();

        $this->assertInstanceOf(
            SPIUserHandler::class,
            $userHandler
        );
        $this->assertInstanceOf(
            UserHandler::class,
            $userHandler
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::userHandler
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
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::sectionHandler
     */
    public function testSectionHandler()
    {
        $handler = $this->getHandlerFixture();
        $sectionHandler = $handler->sectionHandler();

        $this->assertInstanceOf(
            SPISectionHandler::class,
            $sectionHandler
        );
        $this->assertInstanceOf(
            SectionHandler::class,
            $sectionHandler
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::sectionHandler
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
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::urlAliasHandler
     */
    public function testUrlAliasHandler()
    {
        $handler = $this->getHandlerFixture();
        $urlAliasHandler = $handler->urlAliasHandler();

        $this->assertInstanceOf(
            SPIUrlAliasHandler::class,
            $urlAliasHandler
        );
        $this->assertInstanceOf(
            UrlAliasHandler::class,
            $urlAliasHandler
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::urlAliasHandler
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
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::notificationHandler()
     */
    public function testNotificationHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->notificationHandler(),
            $handler->notificationHandler()
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::transactionHandler
     */
    public function testTransactionHandler()
    {
        $handler = $this->getHandlerFixture();
        $transactionHandler = $handler->transactionHandler();

        $this->assertInstanceOf(
            SPITransactionHandler::class,
            $transactionHandler
        );
        $this->assertInstanceOf(
            TransactionHandler::class,
            $transactionHandler
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Handler::transactionHandler
     */
    public function testTransactionHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->transactionHandler(),
            $handler->transactionHandler()
        );
    }

    protected static $legacyHandler;

    /**
     * Returns the Handler.
     *
     * @return Handler
     */
    protected function getHandlerFixture()
    {
        if (!isset(self::$legacyHandler)) {
            $container = $this->getContainer();

            self::$legacyHandler = $container->get('ezpublish.spi.persistence.legacy');
        }

        return self::$legacyHandler;
    }

    protected static $container;

    protected function getContainer()
    {
        if (!isset(self::$container)) {
            $config = include __DIR__ . '/../../../../../../config.php';
            $installDir = $config['install_dir'];

            /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder */
            $containerBuilder = include $config['container_builder_path'];

            /* @var \Symfony\Component\DependencyInjection\Loader\YamlFileLoader $loader */
            $loader->load('search_engines/legacy.yml');

            $containerBuilder->setParameter(
                'languages',
                ['eng-US', 'eng-GB']
            );
            $containerBuilder->setParameter(
                'legacy_dsn',
                $this->getDsn()
            );

            self::$container = new ServiceContainer(
                $containerBuilder,
                $installDir,
                $config['cache_dir'],
                true,
                true
            );
        }

        return self::$container;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler::createFromDSN
     */
    public function testDatabaseInstance()
    {
        $container = $this->getContainer();
        $databaseHandler = $container->get('ezpublish.api.storage_engine.legacy.dbhandler');
        $className = get_class($this->getDatabaseHandler());

        $this->assertTrue(
            $databaseHandler instanceof $className,
            get_class($databaseHandler) . " not of type $className."
        );
    }
}
