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
    protected function getContentSearchHandler(array $fullTextSearchConfiguration = array())
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
                    array(
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
                            $fullTextSearchConfiguration
                        ),
                        new Content\Common\Gateway\CriterionHandler\Field(
                            $this->getDatabaseHandler(),
                            $this->getContentTypeHandler(),
                            $this->getLanguageHandler(),
                            $this->getConverterRegistry(),
                            new Content\Common\Gateway\CriterionHandler\FieldValue\Converter(
                                new Content\Common\Gateway\CriterionHandler\FieldValue\HandlerRegistry(
                                    array(
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
                                    )
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
                    )
                ),
                new Content\Common\Gateway\SortClauseConverter(
                    array(
                        new Content\Common\Gateway\SortClauseHandler\ContentId($this->getDatabaseHandler()),
                    )
                ),
                $this->getLanguageHandler()
            ),
            $this->createMock(LocationGateway::class),
            new Content\WordIndexer\Gateway\DoctrineDatabase(
                $this->getDatabaseHandler(),
                $this->getContentTypeHandler(),
                $this->getDefinitionBasedTransformationProcessor(),
                new Content\WordIndexer\Repository\SearchIndex($this->getDatabaseHandler()),
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
                array(
                    $this->getConverterRegistry(),
                    $this->getLanguageHandler(),
                )
            )
            ->setMethods(array('extractContentInfoFromRows'))
            ->getMock();
        $mapperMock->expects($this->any())
            ->method('extractContentInfoFromRows')
            ->with($this->isType('array'))
            ->will(
                $this->returnCallback(
                    function ($rows) {
                        $contentInfoObjs = array();
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
            ->setMethods(array('loadExternalFieldData'))
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
                array(
                    'filter' => new Criterion\ContentId(10),
                )
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
                array(
                    'filter' => new Criterion\ContentId(10),
                    'offset' => 0,
                    'limit' => 0,
                )
            )
        );

        $this->assertEquals(
            1,
            $result->totalCount
        );
        $this->assertEquals(
            array(),
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
                array(
                    'filter' => new Criterion\ContentId(10),
                    'offset' => 0,
                    'limit' => null,
                )
            )
        );

        $this->assertEquals(
            1,
            $result->totalCount
        );
        $this->assertEquals(
            1,
            count($result->searchHits)
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
                array(
                    'filter' => new Criterion\ContentId(10),
                    'offset' => 1000,
                    'limit' => null,
                )
            )
        );

        $this->assertEquals(
            1,
            $result->totalCount
        );
        $this->assertEquals(
            0,
            count($result->searchHits)
        );
    }

    public function testFindSingle()
    {
        $locator = $this->getContentSearchHandler();

        $contentInfo = $locator->findSingle(new Criterion\ContentId(10));

        $this->assertEquals(10, $contentInfo->id);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindSingleWithNonSearchableField()
    {
        $locator = $this->getContentSearchHandler();
        $locator->findSingle(
            new Criterion\Field(
                'tag_cloud_url',
                Criterion\Operator::EQ,
                'http://nimbus.com'
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindContentWithNonSearchableField()
    {
        $locator = $this->getContentSearchHandler();
        $locator->findContent(
            new Query(
                array(
                    'filter' => new Criterion\Field(
                        'tag_cloud_url',
                        Criterion\Operator::EQ,
                        'http://nimbus.com'
                    ),
                    'sortClauses' => array(new SortClause\ContentId()),
                )
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFindSingleTooMany()
    {
        $locator = $this->getContentSearchHandler();
        $locator->findSingle(new Criterion\ContentId(array(4, 10, 12, 23)));
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testFindSingleZero()
    {
        $locator = $this->getContentSearchHandler();
        $locator->findSingle(new Criterion\ContentId(0));
    }

    public function testContentIdFilter()
    {
        $this->assertSearchResults(
            array(4, 10),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\ContentId(
                            array(1, 4, 10)
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testContentIdFilterCount()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'filter' => new Criterion\ContentId(
                        array(1, 4, 10)
                    ),
                    'limit' => 10,
                )
            )
        );

        $this->assertSame(2, $result->totalCount);
    }

    public function testContentAndCombinatorFilter()
    {
        $this->assertSearchResults(
            array(4),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\ContentId(
                                    array(1, 4, 10)
                                ),
                                new Criterion\ContentId(
                                    array(4, 12)
                                ),
                            )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testContentOrCombinatorFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'filter' => new Criterion\LogicalOr(
                        array(
                            new Criterion\ContentId(
                                array(1, 4, 10)
                            ),
                            new Criterion\ContentId(
                                array(4, 12)
                            ),
                        )
                    ),
                    'limit' => 10,
                )
            )
        );

        $expectedContentIds = array(4, 10, 12);

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
            array(4),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\LogicalAnd(
                            array(
                                new Criterion\ContentId(
                                    array(1, 4, 10)
                                ),
                                new Criterion\LogicalNot(
                                    new Criterion\ContentId(
                                        array(10, 12)
                                    )
                                ),
                            )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testContentSubtreeFilterIn()
    {
        $this->assertSearchResults(
            array(67, 68, 69, 70, 71, 72, 73, 74),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\Subtree(
                            array('/1/2/69/')
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testContentSubtreeFilterEq()
    {
        $this->assertSearchResults(
            array(67, 68, 69, 70, 71, 72, 73, 74),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\Subtree('/1/2/69/'),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testContentTypeIdFilter()
    {
        $this->assertSearchResults(
            array(10, 14, 226),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\ContentTypeId(4),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testContentTypeIdentifierFilter()
    {
        $this->assertSearchResults(
            array(41, 45, 49, 50, 51),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\ContentTypeIdentifier('folder'),
                        'limit' => 5,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                )
            )
        );
    }

    public function testContentTypeGroupFilter()
    {
        $this->assertSearchResults(
            array(4, 10, 11, 12, 13, 14, 42, 225, 226),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\ContentTypeGroupId(2),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testDateMetadataFilterModifiedGreater()
    {
        $this->assertSearchResults(
            array(11, 225, 226),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::GT,
                            1311154214
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testDateMetadataFilterModifiedGreaterOrEqual()
    {
        $this->assertSearchResults(
            array(11, 14, 225, 226),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::GTE,
                            1311154214
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testDateMetadataFilterModifiedIn()
    {
        $this->assertSearchResults(
            array(11, 14, 225, 226),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::IN,
                            array(1311154214, 1311154215)
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testDateMetadataFilterModifiedBetween()
    {
        $this->assertSearchResults(
            array(11, 14, 225, 226),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::MODIFIED,
                            Criterion\Operator::BETWEEN,
                            array(1311154213, 1311154215)
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testDateMetadataFilterCreatedBetween()
    {
        $this->assertSearchResults(
            array(66, 131, 225),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\DateMetadata(
                            Criterion\DateMetadata::CREATED,
                            Criterion\Operator::BETWEEN,
                            array(1299780749, 1311154215)
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testLocationIdFilter()
    {
        $this->assertSearchResults(
            array(4, 65),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\LocationId(array(1, 2, 5)),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testParentLocationIdFilter()
    {
        $this->assertSearchResults(
            array(4, 41, 45, 56, 65),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\ParentLocationId(array(1)),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testRemoteIdFilter()
    {
        $this->assertSearchResults(
            array(4, 10),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\RemoteId(
                            array('f5c88a2209584891056f987fd965b0ba', 'faaeb9be3bd98ed09f606fc16d144eca')
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testLocationRemoteIdFilter()
    {
        $this->assertSearchResults(
            array(4, 65),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\LocationRemoteId(
                            array('3f6d92f8044aed134f32153517850f5a', 'f3e90596361e31d496d4026eb624c983')
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testSectionFilter()
    {
        $this->assertSearchResults(
            array(4, 10, 11, 12, 13, 14, 42, 226),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\SectionId(array(2)),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testStatusFilter()
    {
        $this->assertSearchResults(
            array(4, 10, 11, 12, 13, 14, 41, 42, 45, 49),
            $searchResult = $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        // Status criterion is gone, but this will also match all published
                        'filter' => new Criterion\LogicalNot(
                            new Criterion\ContentId(
                                array(0)
                            )
                        ),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
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
            array(11),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\Field(
                            'name',
                            Criterion\Operator::EQ,
                            'members'
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFieldFilterIn()
    {
        $this->assertSearchResults(
            array(11, 42),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\Field(
                            'name',
                            Criterion\Operator::IN,
                            array('members', 'anonymous users')
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFieldFilterContainsPartial()
    {
        $this->assertSearchResults(
            array(42),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\Field(
                            'name',
                            Criterion\Operator::CONTAINS,
                            'nonymous use'
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFieldFilterContainsSimple()
    {
        $this->assertSearchResults(
            array(77),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\Field(
                            'publish_date',
                            Criterion\Operator::CONTAINS,
                            1174643880
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFieldFilterContainsSimpleNoMatch()
    {
        $this->assertSearchResults(
            array(),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\Field(
                            'publish_date',
                            Criterion\Operator::CONTAINS,
                            1174643
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFieldFilterBetween()
    {
        $this->assertSearchResults(
            array(69, 71, 72),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\Field(
                            'price',
                            Criterion\Operator::BETWEEN,
                            array(10000, 1000000)
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFieldFilterOr()
    {
        $this->assertSearchResults(
            array(11, 69, 71, 72),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\LogicalOr(
                            array(
                                new Criterion\Field(
                                    'name',
                                    Criterion\Operator::EQ,
                                    'members'
                                ),
                                new Criterion\Field(
                                    'price',
                                    Criterion\Operator::BETWEEN,
                                    array(10000, 1000000)
                                ),
                            )
                        ),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFullTextFilter()
    {
        $this->assertSearchResults(
            array(191),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\FullText('applied webpage'),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFullTextWildcardFilter()
    {
        $this->assertSearchResults(
            array(191),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\FullText('applie*'),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFullTextDisabledWildcardFilter()
    {
        $this->assertSearchResults(
            array(),
            $this->getContentSearchHandler(
                array('enableWildcards' => false)
            )->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\FullText('applie*'),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFullTextFilterStopwordRemoval()
    {
        $handler = $this->getContentSearchHandler(
            array(
                'stopWordThresholdFactor' => 0.1,
            )
        );

        $this->assertSearchResults(
            array(),
            $handler->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\FullText('the'),
                        'limit' => 10,
                    )
                )
            )
        );
    }

    public function testFullTextFilterNoStopwordRemoval()
    {
        $handler = $this->getContentSearchHandler(
            array(
                'stopWordThresholdFactor' => 1,
            )
        );

        $result = $handler->findContent(
            new Query(
                array(
                    'filter' => new Criterion\FullText(
                        'the'
                    ),
                    'limit' => 10,
                )
            )
        );

        $this->assertEquals(
            10,
            count(
                array_map(
                    function ($hit) {
                        return $hit->valueObject->id;
                    },
                    $result->searchHits
                )
            )
        );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testFullTextFilterInvalidStopwordThreshold()
    {
        $this->getContentSearchHandler(
            array(
                'stopWordThresholdFactor' => 2,
            )
        );
    }

    public function testObjectStateIdFilter()
    {
        $this->assertSearchResults(
            array(4, 10, 11, 12, 13, 14, 41, 42, 45, 49),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\ObjectStateId(1),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                )
            )
        );
    }

    public function testObjectStateIdFilterIn()
    {
        $this->assertSearchResults(
            array(4, 10, 11, 12, 13, 14, 41, 42, 45, 49),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\ObjectStateId(array(1, 2)),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                )
            )
        );
    }

    public function testLanguageCodeFilter()
    {
        $this->assertSearchResults(
            array(4, 10, 11, 12, 13, 14, 41, 42, 45, 49),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\LanguageCode('eng-US'),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                )
            )
        );
    }

    public function testLanguageCodeFilterIn()
    {
        $this->assertSearchResults(
            array(4, 10, 11, 12, 13, 14, 41, 42, 45, 49),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\LanguageCode(array('eng-US', 'eng-GB')),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                )
            )
        );
    }

    public function testLanguageCodeFilterWithAlwaysAvailable()
    {
        $this->assertSearchResults(
            array(4, 10, 11, 12, 13, 14, 41, 42, 45, 49, 50, 51, 56, 57, 65, 68, 70, 74, 76, 80),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\LanguageCode('eng-GB', true),
                        'limit' => 20,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                )
            )
        );
    }

    public function testVisibilityFilter()
    {
        $this->assertSearchResults(
            array(4, 10, 11, 12, 13, 14, 41, 42, 45, 49),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\Visibility(
                            Criterion\Visibility::VISIBLE
                        ),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterOwnerWrongUserId()
    {
        $this->assertSearchResults(
            array(),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::OWNER,
                            Criterion\Operator::EQ,
                            2
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterOwnerAdministrator()
    {
        $this->assertSearchResults(
            array(4, 10, 11, 12, 13, 14, 41, 42, 45, 49),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::OWNER,
                            Criterion\Operator::EQ,
                            14
                        ),
                        'limit' => 10,
                        'sortClauses' => array(new SortClause\ContentId()),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterOwnerEqAMember()
    {
        $this->assertSearchResults(
            array(223),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::OWNER,
                            Criterion\Operator::EQ,
                            226
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterOwnerInAMember()
    {
        $this->assertSearchResults(
            array(223),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::OWNER,
                            Criterion\Operator::IN,
                            array(226)
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterCreatorEqAMember()
    {
        $this->assertSearchResults(
            array(223),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::MODIFIER,
                            Criterion\Operator::EQ,
                            226
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterCreatorInAMember()
    {
        $this->assertSearchResults(
            array(223),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::MODIFIER,
                            Criterion\Operator::IN,
                            array(226)
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterEqGroupMember()
    {
        $this->assertSearchResults(
            array(223),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::GROUP,
                            Criterion\Operator::EQ,
                            11
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterInGroupMember()
    {
        $this->assertSearchResults(
            array(223),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::GROUP,
                            Criterion\Operator::IN,
                            array(11)
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterEqGroupMemberNoMatch()
    {
        $this->assertSearchResults(
            array(),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::GROUP,
                            Criterion\Operator::EQ,
                            13
                        ),
                    )
                )
            )
        );
    }

    public function testUserMetadataFilterInGroupMemberNoMatch()
    {
        $this->assertSearchResults(
            array(),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\UserMetadata(
                            Criterion\UserMetadata::GROUP,
                            Criterion\Operator::IN,
                            array(13)
                        ),
                    )
                )
            )
        );
    }

    public function testFieldRelationFilterContainsSingle()
    {
        $this->assertSearchResults(
            array(67),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\FieldRelation(
                            'billboard',
                            Criterion\Operator::CONTAINS,
                            array(60)
                        ),
                    )
                )
            )
        );
    }

    public function testFieldRelationFilterContainsSingleNoMatch()
    {
        $this->assertSearchResults(
            array(),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\FieldRelation(
                            'billboard',
                            Criterion\Operator::CONTAINS,
                            array(4)
                        ),
                    )
                )
            )
        );
    }

    public function testFieldRelationFilterContainsArray()
    {
        $this->assertSearchResults(
            array(67),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\FieldRelation(
                            'billboard',
                            Criterion\Operator::CONTAINS,
                            array(60, 75)
                        ),
                    )
                )
            )
        );
    }

    public function testFieldRelationFilterContainsArrayNotMatch()
    {
        $this->assertSearchResults(
            array(),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\FieldRelation(
                            'billboard',
                            Criterion\Operator::CONTAINS,
                            array(60, 64)
                        ),
                    )
                )
            )
        );
    }

    public function testFieldRelationFilterInArray()
    {
        $this->assertSearchResults(
            array(67, 75),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\FieldRelation(
                            'billboard',
                            Criterion\Operator::IN,
                            array(60, 64)
                        ),
                    )
                )
            )
        );
    }

    public function testFieldRelationFilterInArrayNotMatch()
    {
        $this->assertSearchResults(
            array(),
            $this->getContentSearchHandler()->findContent(
                new Query(
                    array(
                        'filter' => new Criterion\FieldRelation(
                            'billboard',
                            Criterion\Operator::IN,
                            array(4, 10)
                        ),
                    )
                )
            )
        );
    }
}
