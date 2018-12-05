<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Tests\Content;

use eZ\Publish\Core\Search\Legacy\Content;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry;
use eZ\Publish\Core\Search\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Mapper as LocationMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper as ContentMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler;

/**
 * Content Search test case for ContentSearchHandler.
 */
class HandlerContentSortTest extends AbstractTestCase
{
    /**
     * Field registry mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $fieldRegistry;

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
        $db = $this->getDatabaseHandler();

        return new Content\Handler(
            new Content\Gateway\DoctrineDatabase(
                $this->getDatabaseHandler(),
                new Content\Common\Gateway\CriteriaConverter(
                    array(
                        new Content\Common\Gateway\CriterionHandler\MatchAll($db),
                        new Content\Common\Gateway\CriterionHandler\LogicalAnd($db),
                        new Content\Common\Gateway\CriterionHandler\SectionId($db),
                        new Content\Common\Gateway\CriterionHandler\ContentTypeIdentifier(
                            $db,
                            $this->getContentTypeHandler()
                        ),
                    )
                ),
                new Content\Common\Gateway\SortClauseConverter(
                    array(
                        new Content\Common\Gateway\SortClauseHandler\DateModified($db),
                        new Content\Common\Gateway\SortClauseHandler\DatePublished($db),
                        new Content\Common\Gateway\SortClauseHandler\SectionIdentifier($db),
                        new Content\Common\Gateway\SortClauseHandler\SectionName($db),
                        new Content\Common\Gateway\SortClauseHandler\ContentName($db),
                        new Content\Common\Gateway\SortClauseHandler\Field(
                            $db,
                            $this->getLanguageHandler(),
                            $this->getContentTypeHandler()
                        ),
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
                    $this->getFieldRegistry(),
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
     * Returns a field registry mock object.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected function getFieldRegistry()
    {
        if (!isset($this->fieldRegistry)) {
            $this->fieldRegistry = $this->getMockBuilder(ConverterRegistry::class)
                ->setConstructorArgs(array())
                ->setMethods(array())
                ->getMock();
        }

        return $this->fieldRegistry;
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

    public function testNoSorting()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'filter' => new Criterion\SectionId(array(2)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(),
                )
            )
        );

        $ids = array_map(
            function ($hit) {
                return $hit->valueObject->id;
            },
            $result->searchHits
        );
        sort($ids);
        $this->assertEquals(
            array(4, 10, 11, 12, 13, 14, 42, 226),
            $ids
        );
    }

    public function testSortDateModified()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'filter' => new Criterion\SectionId(array(2)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(
                        new SortClause\DateModified(),
                    ),
                )
            )
        );

        $this->assertEquals(
            array(4, 12, 13, 42, 10, 14, 11, 226),
            array_map(
                function ($hit) {
                    return $hit->valueObject->id;
                },
                $result->searchHits
            )
        );
    }

    public function testSortDatePublished()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'filter' => new Criterion\SectionId(array(2)),
                    'offset' => 0,
                    'limit' => 10,
                    'sortClauses' => array(
                        new SortClause\DatePublished(),
                    ),
                )
            )
        );

        $this->assertEquals(
            array(4, 10, 11, 12, 13, 14, 226, 42),
            array_map(
                function ($hit) {
                    return $hit->valueObject->id;
                },
                $result->searchHits
            )
        );
    }

    public function testSortSectionIdentifier()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'filter' => new Criterion\SectionId(array(4, 2, 6, 3)),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\SectionIdentifier(),
                    ),
                )
            )
        );

        // First, results of section 2 should appear, then the ones of 3, 4 and 6
        // From inside a specific section, no particular order should be defined
        // the logic is then to have a set of sorted id's to compare with
        // the comparison being done slice by slice.
        $idMapSet = array(
            2 => array(4, 10, 11, 12, 13, 14, 42, 226),
            3 => array(41, 49, 50, 51, 57, 58, 59, 60, 61, 62, 63, 64, 66, 200, 201),
            4 => array(45, 52),
            6 => array(154, 155, 156, 157, 158, 159, 160, 161, 162, 163, 164),
        );
        $contentIds = array_map(
            function ($hit) {
                return $hit->valueObject->id;
            },
            $result->searchHits
        );
        $index = 0;

        foreach ($idMapSet as $idSet) {
            $contentIdsSubset = array_slice($contentIds, $index, $count = count($idSet));
            $index += $count;
            sort($contentIdsSubset);
            $this->assertEquals(
                $idSet,
                $contentIdsSubset
            );
        }
    }

    public function testSortSectionName()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'filter' => new Criterion\SectionId(array(4, 2, 6, 3)),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\SectionName(),
                    ),
                )
            )
        );

        // First, results of section "Media" should appear, then the ones of "Protected",
        // "Setup" and "Users"
        // From inside a specific section, no particular order should be defined
        // the logic is then to have a set of sorted id's to compare with
        // the comparison being done slice by slice.
        $idMapSet = array(
            'media' => array(41, 49, 50, 51, 57, 58, 59, 60, 61, 62, 63, 64, 66, 200, 201),
            'protected' => array(154, 155, 156, 157, 158, 159, 160, 161, 162, 163, 164),
            'setup' => array(45, 52),
            'users' => array(4, 10, 11, 12, 13, 14, 42, 226),
        );
        $contentIds = array_map(
            function ($hit) {
                return $hit->valueObject->id;
            },
            $result->searchHits
        );

        $expectedCount = 0;
        foreach ($idMapSet as $set) {
            $expectedCount += count($set);
        }

        $this->assertEquals($expectedCount, $result->totalCount);

        $index = 0;
        foreach ($idMapSet as $idSet) {
            $contentIdsSubset = array_slice($contentIds, $index, $count = count($idSet));
            $index += $count;
            sort($contentIdsSubset);
            $this->assertEquals(
                $idSet,
                $contentIdsSubset
            );
        }
    }

    public function testSortContentName()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'filter' => new Criterion\SectionId(array(2, 3)),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\ContentName(),
                    ),
                )
            )
        );

        $this->assertEquals(
            array(226, 14, 12, 10, 42, 57, 13, 50, 49, 41, 11, 51, 62, 4, 58, 59, 61, 60, 64, 63, 200, 66, 201),
            array_map(
                function ($hit) {
                    return $hit->valueObject->id;
                },
                $result->searchHits
            )
        );
    }

    public function testSortFieldText()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'filter' => new Criterion\LogicalAnd(
                        array(
                            new Criterion\SectionId(array(1)),
                            new Criterion\ContentTypeIdentifier(array('article')),
                        )
                    ),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\Field('article', 'title', Query::SORT_ASC, 'eng-US'),
                    ),
                )
            )
        );

        // There are several identical titles, need to take care about this
        $idMapSet = array(
            'aenean malesuada ligula' => array(83),
            'aliquam pulvinar suscipit tellus' => array(102),
            'asynchronous publishing' => array(148, 215),
            'canonical links' => array(147, 216),
            'class aptent taciti' => array(88),
            'class aptent taciti sociosqu' => array(82),
            'duis auctor vehicula erat' => array(89),
            'etiam posuere sodales arcu' => array(78),
            'etiam sodales mauris' => array(87),
            'ez publish enterprise' => array(151),
            'fastcgi' => array(144, 218),
            'fusce sagittis sagittis' => array(77),
            'fusce sagittis sagittis urna' => array(81),
            'get involved' => array(107),
            'how to develop with ez publish' => array(127, 211),
            'how to manage ez publish' => array(118, 202),
            'how to use ez publish' => array(108, 193),
            'improved block editing' => array(136),
            'improved front-end editing' => array(139),
            'improved user registration workflow' => array(132),
            'in hac habitasse platea' => array(79),
            'lots of websites, one ez publish installation' => array(130),
            'rest api interface' => array(150, 214),
            'separate content & design in ez publish' => array(191),
            'support for red hat enterprise' => array(145, 217),
            'tutorials for' => array(106),
        );
        $contentIds = array_map(
            function ($hit) {
                return $hit->valueObject->id;
            },
            $result->searchHits
        );
        $index = 0;

        foreach ($idMapSet as $idSet) {
            $contentIdsSubset = array_slice($contentIds, $index, $count = count($idSet));
            $index += $count;
            sort($contentIdsSubset);
            $this->assertEquals(
                $idSet,
                $contentIdsSubset
            );
        }
    }

    public function testSortFieldNumeric()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'filter' => new Criterion\LogicalAnd(
                        array(
                            new Criterion\SectionId(array(1)),
                            new Criterion\ContentTypeIdentifier('product'),
                        )
                    ),
                    'offset' => 0,
                    'limit' => null,
                    'sortClauses' => array(
                        new SortClause\Field('product', 'price', Query::SORT_ASC, 'eng-US'),
                    ),
                )
            )
        );

        $this->assertEquals(
            array(73, 71, 72, 69),
            array_map(
                function ($hit) {
                    return $hit->valueObject->id;
                },
                $result->searchHits
            )
        );
    }
}
