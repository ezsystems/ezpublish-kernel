<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\DoctrineDatabase as ContentTypeGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Mapper as ContentTypeMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Update\Handler as ContentTypeUpdateHandler;

/**
 * Abstract test suite for legacy search.
 */
class AbstractTestCase extends LanguageAwareTestCase
{
    private static $setup;

    /**
     * Field registry mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    private $converterRegistry;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    private $contentTypeHandler;

    /**
     * Only set up once for these read only tests on a large fixture.
     *
     * Skipping the reset-up, since setting up for these tests takes quite some
     * time, which is not required to spent, since we are only reading from the
     * database anyways.
     */
    public function setUp()
    {
        if (!self::$setup) {
            parent::setUp();
            $this->insertDatabaseFixture(__DIR__ . '/../_fixtures/full_dump.php');
            self::$setup = $this->handler;
        } else {
            $this->handler = self::$setup;
            $this->connection = $this->handler->getConnection();
        }
    }

    /**
     * Assert that the elements are.
     */
    protected function assertSearchResults($expectedIds, $searchResult)
    {
        $ids = $this->getIds($searchResult);
        $this->assertEquals($expectedIds, $ids);
    }

    protected function getIds($searchResult)
    {
        $ids = array_map(
            function ($hit) {
                return $hit->valueObject->id;
            },
            $searchResult->searchHits
        );

        sort($ids);

        return $ids;
    }

    protected function getContentTypeHandler()
    {
        if (!isset($this->contentTypeHandler)) {
            // @todo InMemory handler
            $this->contentTypeHandler = new ContentTypeHandler(
                new ContentTypeGateway(
                    $this->getDatabaseHandler(),
                    $this->getDatabaseConnection(),
                    $this->getLanguageMaskGenerator()
                ),
                new ContentTypeMapper($this->getConverterRegistry()),
                $this->createMock(ContentTypeUpdateHandler::class)
            );
        }

        return $this->contentTypeHandler;
    }

    protected function getConverterRegistry()
    {
        if (!isset($this->converterRegistry)) {
            $this->converterRegistry = new ConverterRegistry(
                array(
                    'ezdatetime' => new Converter\DateAndTimeConverter(),
                    'ezinteger' => new Converter\IntegerConverter(),
                    'ezstring' => new Converter\TextLineConverter(),
                    'ezprice' => new Converter\IntegerConverter(),
                    'ezurl' => new Converter\UrlConverter(),
                    'ezrichtext' => new Converter\RichTextConverter(),
                    'ezboolean' => new Converter\CheckboxConverter(),
                    'ezkeyword' => new Converter\KeywordConverter(),
                    'ezauthor' => new Converter\AuthorConverter(),
                    'ezimage' => new Converter\NullConverter(),
                    'ezsrrating' => new Converter\NullConverter(),
                    'ezmultioption' => new Converter\NullConverter(),
                )
            );
        }

        return $this->converterRegistry;
    }
}
