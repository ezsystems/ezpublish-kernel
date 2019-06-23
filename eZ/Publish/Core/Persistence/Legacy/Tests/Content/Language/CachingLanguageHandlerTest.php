<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language\CachingLanguageHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\SPI\Persistence\Content\Language;
use eZ\Publish\SPI\Persistence\Content\Language\CreateStruct as SPILanguageCreateStruct;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as SPILanguageHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler;
use eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache;

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
     * @var \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache
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
            'cache',
            $handler
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::create
     */
    public function testCreate()
    {
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

        $cacheMock->expects($this->once())
            ->method('setMulti')
            ->with($this->equalTo([$languageFixture]));

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
        $handler = $this->getLanguageHandler();

        $innerHandlerMock = $this->getInnerLanguageHandlerMock();
        $cacheMock = $this->getLanguageCacheMock();

        $innerHandlerMock->expects($this->once())
            ->method('update')
            ->with($this->getLanguageFixture());

        $languageFixture = $this->getLanguageFixture();
        $cacheMock->expects($this->once())
            ->method('setMulti')
            ->with($this->equalTo([$languageFixture]));

        $handler->update($languageFixture);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::load
     */
    public function testLoad()
    {
        $handler = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();

        $cacheMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('ez-language-2'))
            ->willReturn($this->getLanguageFixture());

        $result = $handler->load(2);

        $this->assertEquals(
            $this->getLanguageFixture(),
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::load
     */
    public function testLoadFailure()
    {
        $handler = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();
        $innerHandlerMock = $this->getInnerLanguageHandlerMock();

        $cacheMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('ez-language-2'))
            ->willReturn(null);

        $innerHandlerMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo(2))
            ->will(
                $this->throwException(
                    new NotFoundException('Language', 2)
                )
            );

        $this->expectException(APINotFoundException::class);
        $handler->load(2);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::loadByLanguageCode
     */
    public function testLoadByLanguageCode()
    {
        $handler = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();

        $cacheMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('ez-language-code-eng-US'))
            ->willReturn($this->getLanguageFixture());

        $result = $handler->loadByLanguageCode('eng-US');

        $this->assertEquals(
            $this->getLanguageFixture(),
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler::loadByLanguageCode
     */
    public function testLoadByLanguageCodeFailure()
    {
        $handler = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();
        $innerHandlerMock = $this->getInnerLanguageHandlerMock();

        $cacheMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('ez-language-code-eng-US'))
            ->willReturn(null);

        $innerHandlerMock->expects($this->once())
            ->method('loadByLanguageCode')
            ->with($this->equalTo('eng-US'))
            ->will(
                $this->throwException(
                    new NotFoundException('Language', 2)
                )
            );

        $this->expectException(APINotFoundException::class);
        $handler->loadByLanguageCode('eng-US');
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler::loadAll
     */
    public function testLoadAll()
    {
        $handler = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();

        $cacheMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('ez-language-list'))
            ->willReturn([]);

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
        $handler = $this->getLanguageHandler();
        $cacheMock = $this->getLanguageCacheMock();
        $innerHandlerMock = $this->getInnerLanguageHandlerMock();

        $innerHandlerMock->expects($this->once())
            ->method('delete')
            ->with($this->equalTo(2));

        $cacheMock->expects($this->once())
            ->method('deleteMulti')
            ->with($this->equalTo(['ez-language-2', 'ez-language-list']));

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
     * @return \eZ\Publish\SPI\Persistence\Content\Language\Handler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getInnerLanguageHandlerMock()
    {
        if (!isset($this->innerHandlerMock)) {
            $this->innerHandlerMock = $this->createMock(SPILanguageHandler::class);
        }

        return $this->innerHandlerMock;
    }

    /**
     * Returns a mock for the in-memory cache.
     *
     * @return \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getLanguageCacheMock()
    {
        if (!isset($this->languageCacheMock)) {
            $this->languageCacheMock = $this->createMock(InMemoryCache::class);
        }

        return $this->languageCacheMock;
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

        return [$langUs, $langGb];
    }
}
