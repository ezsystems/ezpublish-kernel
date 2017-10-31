<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language\CachingLanguageHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct as SPILanguageCreateStruct;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as SPILanguageHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache;

/**
 * Test case for caching Language Handler.
 */
class CachingLanguageHandlerTest extends TestCase
{
    /**
     * Language handler.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Inner language handler mock.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $innerHandlerMock;

    /**
     * Language cache mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache
     */
    protected $languageCacheMock;

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::__construct
     */
    public function testCtorPropertyInnerHandler()
    {
        $handler = $this->getLanguageHandler();

        $this->assertAttributeSame(
            $this->getInnerLanguageHandlerMock(),
            'innerHandler',
            $handler
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::__construct
     */
    public function testCtorPropertyLanguageCache()
    {
        $handler = $this->getLanguageHandler();

        $this->assertAttributeSame(
            $this->getLanguageCacheMock(),
            'languageCache',
            $handler
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::create
     */
    public function testCreate()
    {
        $this->expectCacheInitialize();

        $handler = $this->getLanguageHandler();
        $innerHandlerMock = $this->getInnerLanguageHandlerMock();
        $cacheMock = $this->getLanguageCacheMock();

        $languageFixture = $this->getLanguageFixture();

        $innerHandlerMock->expects($this->once())
            ->method('create')
            ->with(
                $this->isInstanceOf(
                    SPILanguageCreateStruct::class
                )
            )->will($this->returnValue($languageFixture));

        // Cache has been initialized before
        $cacheMock->expects($this->at(2))
            ->method('store')
            ->with($this->equalTo($languageFixture));

        $createStruct = $this->getCreateStructFixture();

        $result = $handler->create($createStruct);

        $this->assertEquals(
            $languageFixture,
            $result
        );
    }

    /**
     * Returns a Language CreateStruct.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language\CreateStruct
     */
    protected function getCreateStructFixture()
    {
        return new Language\CreateStruct();
    }

    /**
     * Returns a Language.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    protected function getLanguageFixture()
    {
        $language = new Language();
        $language->id = 8;
        $language->languageCode = 'de-DE';

        return $language;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::update
     */
    public function testUpdate()
    {
        $this->expectCacheInitialize();

        $handler = $this->getLanguageHandler();

        $innerHandlerMock = $this->getInnerLanguageHandlerMock();
        $cacheMock = $this->getLanguageCacheMock();

        $innerHandlerMock->expects($this->once())
            ->method('update')
            ->with($this->getLanguageFixture());

        // Cache has been initialized before
        $cacheMock->expects($this->at(2))
            ->method('store')
            ->with($this->getLanguageFixture());

        $handler->update($this->getLanguageFixture());
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::load
     */
    public function testLoad()
    {
        $this->expectCacheInitialize();

        $handler = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();

        $cacheMock->expects($this->once())
            ->method('getById')
            ->with($this->equalTo(2))
            ->will($this->returnValue($this->getLanguageFixture()));

        $result = $handler->load(2);

        $this->assertEquals(
            $this->getLanguageFixture(),
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::load
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadFailure()
    {
        $this->expectCacheInitialize();

        $handler = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();

        $cacheMock->expects($this->once())
            ->method('getById')
            ->with($this->equalTo(2))
            ->will(
                $this->throwException(
                    new NotFoundException('Language', 2)
                )
            );

        $result = $handler->load(2);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::loadByLanguageCode
     */
    public function testLoadByLanguageCode()
    {
        $this->expectCacheInitialize();

        $handler = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();

        $cacheMock->expects($this->once())
            ->method('getByLocale')
            ->with($this->equalTo('eng-US'))
            ->will($this->returnValue($this->getLanguageFixture()));

        $result = $handler->loadByLanguageCode('eng-US');

        $this->assertEquals(
            $this->getLanguageFixture(),
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::loadByLanguageCode
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadByLanguageCodeFailure()
    {
        $this->expectCacheInitialize();

        $handler = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();

        $cacheMock->expects($this->once())
            ->method('getByLocale')
            ->with($this->equalTo('eng-US'))
            ->will(
                $this->throwException(
                    new NotFoundException('Language', 'eng-US')
                )
            );

        $result = $handler->loadByLanguageCode('eng-US');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler::loadAll
     */
    public function testLoadAll()
    {
        $this->expectCacheInitialize();

        $handler = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();

        $cacheMock->expects($this->once())
            ->method('getAll')
            ->will($this->returnValue(array()));

        $result = $handler->loadAll();

        $this->assertInternalType(
            'array',
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::delete
     */
    public function testDelete()
    {
        $this->expectCacheInitialize();

        $handler = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();
        $innerHandlerMock = $this->getInnerLanguageHandlerMock();

        $innerHandlerMock->expects($this->once())
            ->method('delete')
            ->with($this->equalTo(2));

        $cacheMock->expects($this->once())
            ->method('remove')
            ->with($this->equalTo(2));

        $result = $handler->delete(2);
    }

    /**
     * Returns the language handler to test.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
     */
    protected function getLanguageHandler()
    {
        if (!isset($this->languageHandler)) {
            $this->languageHandler = new CachingHandler(
                $this->getInnerLanguageHandlerMock(),
                $this->getLanguageCacheMock()
            );
        }

        return $this->languageHandler;
    }

    /**
     * Returns a mock for the inner language handler.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected function getInnerLanguageHandlerMock()
    {
        if (!isset($this->innerHandlerMock)) {
            $this->innerHandlerMock = $this->createMock(SPILanguageHandler::class);
        }

        return $this->innerHandlerMock;
    }

    /**
     * Returns a mock for the language cache.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache
     */
    protected function getLanguageCacheMock()
    {
        if (!isset($this->languageCacheMock)) {
            $this->languageCacheMock = $this->createMock(Cache::class);
        }

        return $this->languageCacheMock;
    }

    /**
     * Adds expectation for cache initialize to mocks.
     */
    protected function expectCacheInitialize()
    {
        $innerHandlerMock = $this->getInnerLanguageHandlerMock();
        $innerHandlerMock->expects($this->once())
            ->method('loadAll')
            ->will($this->returnValue($this->getLanguagesFixture()));
    }

    /**
     * Returns an array with 2 languages.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language[]
     */
    protected function getLanguagesFixture()
    {
        $langUs = new Language();
        $langUs->id = 2;
        $langUs->languageCode = 'eng-US';
        $langUs->name = 'English (American)';
        $langUs->isEnabled = true;

        $langGb = new Language();
        $langGb->id = 4;
        $langGb->languageCode = 'eng-GB';
        $langGb->name = 'English (United Kingdom)';
        $langGb->isEnabled = true;

        return array($langUs, $langGb);
    }
}
