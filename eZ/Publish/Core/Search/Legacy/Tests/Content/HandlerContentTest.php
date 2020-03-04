<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence;
use eZ\Publish\Core\Search\Legacy\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\Core\Search\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler;

/**
 * Content Search test case for ContentSearchHandler.
 */
class HandlerContentTest extends AbstractTestCase
{
    /**
     * Returns the content search handler to test.
     *
     * This method returns a fully functional search handler to perform tests
     * on.
     *
     * @param array $fullTextSearchConfiguration
     *
     * @return \eZ\Publish\Core\Search\Legacy\Content\Handler
     */
    protected function getContentSearchHandler(array $fullTextSearchConfiguration = [])
    {
        $transformationProcessor = new Persistence\TransformationProcessor\DefinitionBased(
            new Persistence\TransformationProcessor\DefinitionBased\Parser(),
            new Persistence\TransformationProcessor\PcreCompiler(
                new Persistence\Utf8Converter()
            ),
            glob(__DIR__ . '/../../../../Persistence/Tests/TransformationProcessor/_fixtures/transformations/*.tr')
        );
        $commaSeparatedCollectionValueHandler = new Content\Common\Gateway\CriterionHandler\FieldValue\Handler\Collection(
            $this->getDatabaseHandler(),
            $transformationProcessor,
            ','
        );
        $hyphenSeparatedCollectionValueHandler = new Content\Common\Gateway\CriterionHandler\FieldValue\Handler\Collection(
            $this->getDatabaseHandler(),
            $transformationProcessor,
            '-'
        );
        $simpleValueHandler = new Content\Common\Gateway\CriterionHandler\FieldValue\Handler\Simple(
            $this->getDatabaseHandler(),
            $transformationProcessor
        );
        $compositeValueHandler = new Content\Common\Gateway\CriterionHandler\FieldValue\Handler\Composite(
            $this->getDatabaseHandler(),
            $transformationProcessor
        );

        return new Content\Handler(
            new Content\Gateway\DoctrineDatabase(
                $this->getDatabaseHandler(),
                new Content\Common\Gateway\CriteriaConverter(
                    [
                        new Content\Common\Gateway\CriterionHandler\ContentId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\LogicalNot(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\LogicalAnd(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\LogicalOr(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Gateway\CriterionHandler\Subtree(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\ContentTypeId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\ContentTypeIdentifier(
                            $this->getDatabaseHandler(),
                            $this->getContentTypeHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\ContentTypeGroupId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\DateMetadata(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Gateway\CriterionHandler\LocationId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Gateway\CriterionHandler\ParentLocationId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\RemoteId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Gateway\CriterionHandler\LocationRemoteId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\SectionId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\FullText(
                            $this->getDatabaseHandler(),
                            $transformationProcessor,
                            $this->getLanguageMaskGenerator(),
                            $fullTextSearchConfiguration
                        ),
                        new Content\Common\Gateway\CriterionHandler\Field(
                            $this->getDatabaseHandler(),
                            $this->getContentTypeHandler(),
                            $this->getLanguageHandler(),
                            $this->getConverterRegistry(),
                            new Content\Common\Gateway\CriterionHandler\FieldValue\Converter(
                                new Content\Common\Gateway\CriterionHandler\FieldValue\HandlerRegistry(
                                    [
                                        'ezboolean' => $simpleValueHandler,
                                        'ezcountry' => $commaSeparatedCollectionValueHandler,
                                        'ezdate' => $simpleValueHandler,
                                        'ezdatetime' => $simpleValueHandler,
                                        'ezemail' => $simpleValueHandler,
                                        'ezinteger' => $simpleValueHandler,
                                        'ezobjectrelation' => $simpleValueHandler,
                                        'ezobjectrelationlist' => $commaSeparatedCollectionValueHandler,
                                        'ezselection' => $hyphenSeparatedCollectionValueHandler,
                                        'eztime' => $simpleValueHandler,
                                    ]
                                ),
                                $compositeValueHandler
                            ),
                            $transformationProcessor
                        ),
                        new Content\Common\Gateway\CriterionHandler\ObjectStateId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\LanguageCode(
                            $this->getDatabaseHandler(),
                            $this->getLanguageMaskGenerator()
                        ),
                        new Content\Gateway\CriterionHandler\Visibility(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\MatchAll(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\UserMetadata(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Common\Gateway\CriterionHandler\FieldRelation(
                            $this->getDatabaseHandler(),
                            $this->getContentTypeHandler(),
                            $this->getLanguageHandler()
                        ),
                    ]
                ),
                new Content\Common\Gateway\SortClauseConverter(
                    [
                        new Content\Common\Gateway\SortClauseHandler\ContentId($this->getDatabaseHandler()),
                    ]
                ),
                $this->getLanguageHandler()
            ),
            $this->createMock(LocationGateway::class),
            new Content\WordIndexer\Gateway\DoctrineDatabase(
                $this->getDatabaseHandler(),
                $this->getContentTypeHandler(),
                $this->getDefinitionBasedTransformationProcessor(),
                new Content\WordIndexer\Repository\SearchIndex($this->getDatabaseHandler()),
                $this->getLanguageMaskGenerator(),
                $this->getFullTextSearchConfiguration()
            ),
            $this->getContentMapperMock(),
            $this->createMock(LocationMapper::class),
            $this->getLanguageHandler(),
            $this->getFullTextMapper($this->getContentTypeHandler())
        );
    }

    /**
     * Returns a content mapper mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getContentMapperMock()
    {
        $mapperMock = $this->getMockBuilder(ContentMapper::class)
            ->setConstructorArgs(
                [
                    $this->getConverterRegistry(),
                    $this->getLanguageHandler(),
                ]
            )
            ->setMethods(['extractContentInfoFromRows'])
            ->getMock();
        $mapperMock->expects($this->any())
            ->method('extractContentInfoFromRows')
            ->with($this->isType('array'))
            ->will(
                $this->returnCallback(
                    function ($rows) {
                        $contentInfoObjs = [];
                        foreach ($rows as $row) {
                            $contentId = (int)$row['id'];
                            if (!isset($contentInfoObjs[$contentId])) {
                                $contentInfoObjs[$contentId] = new ContentInfo();
                                $contentInfoObjs[$contentId]->id = $contentId;
                            }
                        }

                        return array_values($contentInfoObjs);
                    }
                )
            );

        return $mapperMock;
    }

    /**
     * Returns a content field handler mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected function getContentFieldHandlerMock()
    {
        return $this->getMockBuilder(FieldHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadExternalFieldData'])
            ->getMock();
    }

    /**
     * Bug #80.
     */
    public function testFindWithoutOffsetLimit()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                [
                    'filter' => new Criterion\ContentId(10),
                ]
            )
        );

        $this->assertEquals(
            1,
            $result->totalCount
        );
    }

    /**
     * Bug #81, bug #82.
     */
    public function testFindWithZeroLimit()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                [
                    'filter' => new Criterion\ContentId(10),
                    'offset' => 0,
                    'limit' => 0,
                ]
            )
        );

        $this->assertEquals(
            1,
            $result->totalCount
        );
        $this->assertEquals(
            [],
            $result->searchHits
        );
    }

    /**
     * Issue with PHP_MAX_INT limit overflow in databases.
     */
    public function testFindWithNullLimit()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                [
                    'filter' => new Criterion\ContentId(10),
                    'offset' => 0,
                    'limit' => null,
                ]
            )
        );

        $this->assertEquals(
            1,
            $result->totalCount
        );
        $this->assertCount(
            1,
            $result->searchHits
        );
    }

    /**
     * Issue with offsetting to the nonexistent results produces \ezcQueryInvalidParameterException exception.
     */
    public function testFindWithOffsetToNonexistent()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                [
                    'filter' => new Criterion\ContentId(10),
                    'offset' => 1000,
                    'limit' => null,
                ]
            )
        );

        $this->assertEquals(
            1,
            $result->totalCount
        );
        $this->assertCount(
            0,
            $result->searchHits
        );
    }

    public function testFindSingle()
    {
        $locator = $this->getContentSearchHandler();

        $contentInfo = $locator->findSingle(new Criterion\ContentId(10));

        $this->assertEquals(10, $contentInfo->id);
    }

    public function testFindSingleWithNonSearchableField()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException::class);

        $locator = $this->getContentSearchHandler();
        $locator->findSingle(
            new Criterion\Field(
                'tag_cloud_url',
                Criterion\Operator::EQ,
                'http://nimbus.com'
            )
        );
    }

    public function testFindContentWithNonSearchableField()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException::class);

        $locator = $this->getContentSearchHandler();
        $locator->findContent(
            new Query(
                [
                    'filter' => new Criterion\Field(
                        'tag_cloud_url',
                        Criterion\Operator::EQ,
                        'http://nimbus.com'
                    ),
                    'sortClauses' => [new SortClause\ContentId()],
                ]
            )
        );
    }

    public function testFindSingleTooMany()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException::class);

        $locator = $this->getContentSearchHandler();
        $locator->findSingle(new Criterion\ContentId([4, 10, 12, 23]));
    }

    public function testFindSingleZero()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $locator = $this->getContentSearchHandler();
        $locator->findSingle(new Criterion\ContentId(0));
    }

    public function testContentIdFilter()
    {
        $this->assertSearchResults(
            [4, 10],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\ContentId(
                            [1, 4, 10]
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testContentIdFilterCount()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                [
                    'filter' => new Criterion\ContentId(
                        [1, 4, 10]
                    ),
                    'limit' => 10,
                ]
            )
        );

        $this->assertSame(2, $result->totalCount);
    }

    public function testContentAndCombinatorFilter()
    {
        $this->assertSearchResults(
            [4],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\LogicalAnd(
                            [
                                new Criterion\ContentId(
                                    [1, 4, 10]
                                ),
                                new Criterion\ContentId(
                                    [4, 12]
                                ),
                            ]
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testContentOrCombinatorFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                [
                    'filter' => new Criterion\LogicalOr(
                        [
                            new Criterion\ContentId(
                                [1, 4, 10]
                            ),
                            new Criterion\ContentId(
                                [4, 12]
                            ),
                        ]
                    ),
                    'limit' => 10,
                ]
            )
        );

        $expectedContentIds = [4, 10, 12];

        $this->assertEquals(
            count($expectedContentIds),
            count($result->searchHits)
        );
        foreach ($result->searchHits as $hit) {
            $this->assertContains(
                $hit->valueObject->id,
                $expectedContentIds
            );
        }
    }

    public function testContentNotCombinatorFilter()
    {
        $this->assertSearchResults(
            [4],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\LogicalAnd(
                            [
                                new Criterion\ContentId(
                                    [1, 4, 10]
                                ),
                                new Criterion\LogicalNot(
                                    new Criterion\ContentId(
                                        [10, 12]
                                    )
                                ),
                            ]
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testContentSubtreeFilterIn()
    {
        $this->assertSearchResults(
            [67, 68, 69, 70, 71, 72, 73, 74],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\Subtree(
                            ['/1/2/69/']
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testContentSubtreeFilterEq()
    {
        $this->assertSearchResults(
            [67, 68, 69, 70, 71, 72, 73, 74],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\Subtree('/1/2/69/'),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testContentTypeIdFilter()
    {
        $this->assertSearchResults(
            [10, 14, 226],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\ContentTypeId(4),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testContentTypeIdentifierFilter()
    {
        $this->assertSearchResults(
            [41, 45, 49, 50, 51],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\ContentTypeIdentifier('folder'),
                        'limit' => 5,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testContentTypeGroupFilter()
    {
        $this->assertSearchResults(
            [4, 10, 11, 12, 13, 14, 42, 225, 226],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\ContentTypeGroupId(2),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testDateMetadataFilterModifiedGreater()
    {
        $this->assertSearchResults(
            [11, 225, 226],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::GT,
                            1311154214
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testDateMetadataFilterModifiedGreaterOrEqual()
    {
        $this->assertSearchResults(
            [11, 14, 225, 226],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::GTE,
                            1311154214
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testDateMetadataFilterModifiedIn()
    {
        $this->assertSearchResults(
            [11, 14, 225, 226],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::IN,
                            [1311154214, 1311154215]
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testDateMetadataFilterModifiedBetween()
    {
        $this->assertSearchResults(
            [11, 14, 225, 226],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::BETWEEN,
                            [1311154213, 1311154215]
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testDateMetadataFilterCreatedBetween()
    {
        $this->assertSearchResults(
            [66, 131, 225],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::CREATED,
                            Criterion\Operator::BETWEEN,
                            [1299780749, 1311154215]
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testLocationIdFilter()
    {
        $this->assertSearchResults(
            [4, 65],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\LocationId([1, 2, 5]),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testParentLocationIdFilter()
    {
        $this->assertSearchResults(
            [4, 41, 45, 56, 65],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\ParentLocationId([1]),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testRemoteIdFilter()
    {
        $this->assertSearchResults(
            [4, 10],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\RemoteId(
                            ['f5c88a2209584891056f987fd965b0ba', 'faaeb9be3bd98ed09f606fc16d144eca']
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testLocationRemoteIdFilter()
    {
        $this->assertSearchResults(
            [4, 65],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\LocationRemoteId(
                            ['3f6d92f8044aed134f32153517850f5a', 'f3e90596361e31d496d4026eb624c983']
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testSectionFilter()
    {
        $this->assertSearchResults(
            [4, 10, 11, 12, 13, 14, 42, 226],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\SectionId([2]),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testStatusFilter()
    {
        $this->assertSearchResults(
            [4, 10, 11, 12, 13, 14, 41, 42, 45, 49],
            $searchResult = $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        // Status criterion is gone, but this will also match all published
                        'filter' => new Criterion\LogicalNot(
                            new Criterion\ContentId(
                                [0]
                            )
                        ),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );

        $this->assertEquals(
            185,
            $searchResult->totalCount
        );
    }

    public function testFieldFilter()
    {
        $this->assertSearchResults(
            [11],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\Field(
                            'name',
                            Criterion\Operator::EQ,
                            'members'
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testFieldFilterIn()
    {
        $this->assertSearchResults(
            [11, 42],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\Field(
                            'name',
                            Criterion\Operator::IN,
                            ['members', 'anonymous users']
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testFieldFilterContainsPartial()
    {
        $this->assertSearchResults(
            [42],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\Field(
                            'name',
                            Criterion\Operator::CONTAINS,
                            'nonymous use'
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testFieldFilterContainsSimple()
    {
        $this->assertSearchResults(
            [77],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\Field(
                            'publish_date',
                            Criterion\Operator::CONTAINS,
                            1174643880
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testFieldFilterContainsSimpleNoMatch()
    {
        $this->assertSearchResults(
            [],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\Field(
                            'publish_date',
                            Criterion\Operator::CONTAINS,
                            1174643
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testFieldFilterBetween()
    {
        $this->assertSearchResults(
            [186, 187],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\Field(
                            'publication_date',
                            Criterion\Operator::BETWEEN,
                            [1190000000, 1200000000]
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testFieldFilterOr()
    {
        $this->assertSearchResults(
            [11, 186, 187],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\LogicalOr(
                            [
                                new Criterion\Field(
                                    'name',
                                    Criterion\Operator::EQ,
                                    'members'
                                ),
                                new Criterion\Field(
                                    'publication_date',
                                    Criterion\Operator::BETWEEN,
                                    [1190000000, 1200000000]
                                ),
                            ]
                        ),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testFullTextFilter()
    {
        $this->assertSearchResults(
            [191],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\FullText('applied webpage'),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testFullTextWildcardFilter()
    {
        $this->assertSearchResults(
            [191],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\FullText('applie*'),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testFullTextDisabledWildcardFilter()
    {
        $this->assertSearchResults(
            [],
            $this->getContentSearchHandler(
                ['enableWildcards' => false]
            )->findContent(
                new Query(
                    [
                        'filter' => new Criterion\FullText('applie*'),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testFullTextFilterStopwordRemoval()
    {
        $handler = $this->getContentSearchHandler(
            [
                'stopWordThresholdFactor' => 0.1,
            ]
        );

        $this->assertSearchResults(
            [],
            $handler->findContent(
                new Query(
                    [
                        'filter' => new Criterion\FullText('the'),
                        'limit' => 10,
                    ]
                )
            )
        );
    }

    public function testFullTextFilterNoStopwordRemoval()
    {
        $handler = $this->getContentSearchHandler(
            [
                'stopWordThresholdFactor' => 1,
            ]
        );

        $result = $handler->findContent(
            new Query(
                [
                    'filter' => new Criterion\FullText(
                        'the'
                    ),
                    'limit' => 10,
                ]
            )
        );

        $this->assertCount(
            10,

                array_map(
                    function ($hit) {
                        return $hit->valueObject->id;
                    },
                    $result->searchHits
                )
        );
    }

    public function testFullTextFilterInvalidStopwordThreshold()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\InvalidArgumentException::class);

        $this->getContentSearchHandler(
            [
                'stopWordThresholdFactor' => 2,
            ]
        );
    }

    public function testObjectStateIdFilter()
    {
        $this->assertSearchResults(
            [4, 10, 11, 12, 13, 14, 41, 42, 45, 49],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\ObjectStateId(1),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testObjectStateIdFilterIn()
    {
        $this->assertSearchResults(
            [4, 10, 11, 12, 13, 14, 41, 42, 45, 49],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\ObjectStateId([1, 2]),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testLanguageCodeFilter()
    {
        $this->assertSearchResults(
            [4, 10, 11, 12, 13, 14, 41, 42, 45, 49],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\LanguageCode('eng-US'),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testLanguageCodeFilterIn()
    {
        $this->assertSearchResults(
            [4, 10, 11, 12, 13, 14, 41, 42, 45, 49],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\LanguageCode(['eng-US', 'eng-GB']),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testLanguageCodeFilterWithAlwaysAvailable()
    {
        $this->assertSearchResults(
            [4, 10, 11, 12, 13, 14, 41, 42, 45, 49, 50, 51, 56, 57, 65, 68, 70, 74, 76, 80],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\LanguageCode('eng-GB', true),
                        'limit' => 20,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testVisibilityFilter()
    {
        $this->assertSearchResults(
            [4, 10, 11, 12, 13, 14, 41, 42, 45, 49],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\Visibility(
                            Criterion\Visibility::VISIBLE
                        ),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testUserMetadataFilterOwnerWrongUserId()
    {
        $this->assertSearchResults(
            [],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::OWNER,
                            Criterion\Operator::EQ,
                            2
                        ),
                    ]
                )
            )
        );
    }

    public function testUserMetadataFilterOwnerAdministrator()
    {
        $this->assertSearchResults(
            [4, 10, 11, 12, 13, 14, 41, 42, 45, 49],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::OWNER,
                            Criterion\Operator::EQ,
                            14
                        ),
                        'limit' => 10,
                        'sortClauses' => [new SortClause\ContentId()],
                    ]
                )
            )
        );
    }

    public function testUserMetadataFilterOwnerEqAMember()
    {
        $this->assertSearchResults(
            [223],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::OWNER,
                            Criterion\Operator::EQ,
                            226
                        ),
                    ]
                )
            )
        );
    }

    public function testUserMetadataFilterOwnerInAMember()
    {
        $this->assertSearchResults(
            [223],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::OWNER,
                            Criterion\Operator::IN,
                            [226]
                        ),
                    ]
                )
            )
        );
    }

    public function testUserMetadataFilterCreatorEqAMember()
    {
        $this->assertSearchResults(
            [223],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::MODIFIER,
                            Criterion\Operator::EQ,
                            226
                        ),
                    ]
                )
            )
        );
    }

    public function testUserMetadataFilterCreatorInAMember()
    {
        $this->assertSearchResults(
            [223],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::MODIFIER,
                            Criterion\Operator::IN,
                            [226]
                        ),
                    ]
                )
            )
        );
    }

    public function testUserMetadataFilterEqGroupMember()
    {
        $this->assertSearchResults(
            [223],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::GROUP,
                            Criterion\Operator::EQ,
                            11
                        ),
                    ]
                )
            )
        );
    }

    public function testUserMetadataFilterInGroupMember()
    {
        $this->assertSearchResults(
            [223],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::GROUP,
                            Criterion\Operator::IN,
                            [11]
                        ),
                    ]
                )
            )
        );
    }

    public function testUserMetadataFilterEqGroupMemberNoMatch()
    {
        $this->assertSearchResults(
            [],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::GROUP,
                            Criterion\Operator::EQ,
                            13
                        ),
                    ]
                )
            )
        );
    }

    public function testUserMetadataFilterInGroupMemberNoMatch()
    {
        $this->assertSearchResults(
            [],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::GROUP,
                            Criterion\Operator::IN,
                            [13]
                        ),
                    ]
                )
            )
        );
    }

    public function testFieldRelationFilterContainsSingle()
    {
        $this->assertSearchResults(
            [67],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\FieldRelation(
                            'billboard',
                            Criterion\Operator::CONTAINS,
                            [60]
                        ),
                    ]
                )
            )
        );
    }

    public function testFieldRelationFilterContainsSingleNoMatch()
    {
        $this->assertSearchResults(
            [],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\FieldRelation(
                            'billboard',
                            Criterion\Operator::CONTAINS,
                            [4]
                        ),
                    ]
                )
            )
        );
    }

    public function testFieldRelationFilterContainsArray()
    {
        $this->assertSearchResults(
            [67],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\FieldRelation(
                            'billboard',
                            Criterion\Operator::CONTAINS,
                            [60, 75]
                        ),
                    ]
                )
            )
        );
    }

    public function testFieldRelationFilterContainsArrayNotMatch()
    {
        $this->assertSearchResults(
            [],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\FieldRelation(
                            'billboard',
                            Criterion\Operator::CONTAINS,
                            [60, 64]
                        ),
                    ]
                )
            )
        );
    }

    public function testFieldRelationFilterInArray()
    {
        $this->assertSearchResults(
            [67, 75],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\FieldRelation(
                            'billboard',
                            Criterion\Operator::IN,
                            [60, 64]
                        ),
                    ]
                )
            )
        );
    }

    public function testFieldRelationFilterInArrayNotMatch()
    {
        $this->assertSearchResults(
            [],
            $this->getContentSearchHandler()->findContent(
                new Query(
                    [
                        'filter' => new Criterion\FieldRelation(
                            'billboard',
                            Criterion\Operator::IN,
                            [4, 10]
                        ),
                    ]
                )
            )
        );
    }
}
