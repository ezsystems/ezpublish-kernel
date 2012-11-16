<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\SearchHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder,
    eZ\Publish\Core\Persistence\Legacy\Content,
    eZ\Publish\SPI\Persistence\Content as ContentObject,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\API\Repository\Values\Content\Query,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\Integer,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\TextLine;

/**
 * Test case for ContentSearchHandler
 */
class SearchHandlerTest extends LanguageAwareTestCase
{
    protected static $setUp = false;

    /**
     * Field registry mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected $fieldRegistry;

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }

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
            $this->insertDatabaseFixture( __DIR__ . '/SearchHandler/_fixtures/full_dump.php' );
            self::$setUp = $this->handler;
        }
        else
        {
            $this->handler = self::$setUp;
        }
    }

    /**
     * Returns the content search handler to test
     *
     * This method returns a fully functional search handler to perform tests
     * on.
     *
     * @param array $fullTextSearchConfiguration
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler
     */
    protected function getContentSearchHandler( array $fullTextSearchConfiguration = array() )
    {
        $rules = array();
        foreach ( glob( __DIR__ . '/SearchHandler/_fixtures/transformations/*.tr' ) as $file )
        {
            $rules[] = str_replace( self::getInstallationDir(), '', $file );
        }

        return new Content\Search\Handler(
            new Content\Search\Gateway\EzcDatabase(
                $this->getDatabaseHandler(),
                new Content\Search\Gateway\CriteriaConverter(
                    array(
                        new Content\Search\Gateway\CriterionHandler\ContentId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\LogicalNot(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\LogicalAnd(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\LogicalOr(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\Subtree(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\ContentTypeId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\ContentTypeGroupId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\DateMetadata(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\LocationId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\ParentLocationId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\RemoteId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\LocationRemoteId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\SectionId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\Status(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\FullText(
                            $this->getDatabaseHandler(),
                            new Content\Search\TransformationProcessor\DefinitionBased(
                                new Content\Search\TransformationProcessor\DefinitionBased\Parser( self::getInstallationDir() ),
                                new Content\Search\TransformationProcessor\PcreCompiler(
                                    new Content\Search\Utf8Converter()
                                ),
                                $rules
                            ),
                            $fullTextSearchConfiguration
                        ),
                        new Content\Search\Gateway\CriterionHandler\Field(
                            $this->getDatabaseHandler(),
                            $this->fieldRegistry = new ConverterRegistry(
                                array(
                                    'ezint' => new Integer(),
                                    'ezstring' => new TextLine(),
                                    'ezprice' => new Integer()
                                )
                            )
                        ),
                        new Content\Search\Gateway\CriterionHandler\ObjectStateId(
                            $this->getDatabaseHandler()
                        ),
                        new Content\Search\Gateway\CriterionHandler\LanguageCode(
                            $this->getDatabaseHandler(),
                            $this->getLanguageMaskGenerator()
                        ),
                        new Content\Search\Gateway\CriterionHandler\Visibility(
                            $this->getDatabaseHandler()
                        ),
                    )
                ),
                new Content\Search\Gateway\SortClauseConverter(),
                new QueryBuilder( $this->getDatabaseHandler() ),
                $this->getLanguageHandler(),
                $this->getLanguageMaskGenerator()
            ),
            $this->getContentMapperMock(),
            $this->getContentFieldHandlerMock()
        );
    }

    /**
     * Returns a content mapper mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getContentMapperMock()
    {
        $mapperMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Mapper',
            array( 'extractContentFromRows' ),
            array(
                $this->fieldRegistry,
                $this->getLanguageHandler()
            )
        );
        $mapperMock->expects( $this->any() )
            ->method( 'extractContentFromRows' )
            ->with( $this->isType( 'array' ) )
            ->will(
                $this->returnCallback(
                    function ( $rows )
                    {
                        $contentObjs = array();
                        foreach ( $rows as $row )
                        {
                            $contentId = (int)$row['ezcontentobject_id'];
                            if ( !isset( $contentObjs[$contentId] ) )
                            {
                                $contentObjs[$contentId] = new ContentObject();
                                $contentObjs[$contentId]->versionInfo = new VersionInfo;
                                $contentObjs[$contentId]->versionInfo->contentInfo = new ContentInfo;
                                $contentObjs[$contentId]->versionInfo->contentInfo->id = $contentId;
                            }
                        }
                        return array_values( $contentObjs );
                    }
                )
            );
        return $mapperMock;
    }

    /**
     * Returns a content field handler mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected function getContentFieldHandlerMock()
    {
        return $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldHandler',
            array( 'loadExternalFieldData' ),
            array(),
            '',
            false
        );
    }

    /**
     * Bug #80
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase::find
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler::find
     */
    public function testFindWithoutOffsetLimit()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\ContentId( 10 )
        ) ) );

        $this->assertEquals(
            1,
            $result->totalCount
        );
    }

    /**
     * Bug #81, bug #82
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase::find
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler::find
     */
    public function testFindWithZeroLimit()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\ContentId( 10 ),
            'offset'    => 0,
            'limit'     => 0,
        ) ) );

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
     * Issue with PHP_MAX_INT limit overflow in databases
     *
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase::find
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler::find
     */
    public function testFindWithNullLimit()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\ContentId( 10 ),
            'offset'    => 0,
            'limit'     => null,
        ) ) );

        $this->assertEquals(
            1,
            $result->totalCount
        );
        $this->assertEquals(
            1,
            count( $result->searchHits )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase::find
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler::find
     */
    public function testFindWithExistingLanguageFields()
    {
        $this->markTestSkipped( "Translation filters are currently not supported by new search API." );

        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion'    => new Criterion\ContentId( 11 ),
            'offset'       => 0,
            'limit'        => null,
            'translations' => array( 'eng-US' )
        ) ) );

        $this->assertEquals(
            1,
            $result->totalCount
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase::find
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler::find
     */
    public function testFindWithMissingLanguageFields()
    {
        $this->markTestSkipped( "Translation filters are currently not supported by new search API." );

        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\ContentId( 4 ),
            'offset'       => 0,
            'limit'        => null,
            'translations' => array( 'eng-GB' )
        ) ) );

        $this->assertEquals(
            0,
            $result->totalCount
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler::findSingle
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriteriaConverter
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler
     */
    public function testFindSingle()
    {
        $locator = $this->getContentSearchHandler();

        $content = $locator->findSingle( new Criterion\ContentId( 10 ) );

        $this->assertEquals( 10, $content->versionInfo->contentInfo->id );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler::findSingle
     */
    public function testFindSingleTooMany()
    {
        $locator = $this->getContentSearchHandler();
        $locator->findSingle( new Criterion\ContentId( array( 4, 10, 12, 23 ) ) );
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler::findSingle
     */
    public function testFindSingleZero()
    {
        $locator = $this->getContentSearchHandler();
        $locator->findSingle( new Criterion\ContentId( 0 ) );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\ContentId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testContentIdFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\ContentId(
                array( 1, 4, 10 )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 10 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\ContentId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testContentIdFilterCount()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\ContentId(
                array( 1, 4, 10 )
            ),
            'limit' => 10,
        ) ) );

        $this->assertSame( 2, $result->totalCount );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\LogicalAnd
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testContentAndCombinatorFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\LogicalAnd(
                array(
                    new Criterion\ContentId(
                        array( 1, 4, 10 )
                    ),
                    new Criterion\ContentId(
                        array( 4, 12 )
                    ),
                )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\LogicalOr
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testContentOrCombinatorFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\LogicalOr(
                array(
                    new Criterion\ContentId(
                        array( 1, 4, 10 )
                    ),
                    new Criterion\ContentId(
                        array( 4, 12 )
                    ),
                )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 10, 12 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\LogicalNot
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testContentNotCombinatorFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\LogicalAnd(
                array(
                    new Criterion\ContentId(
                        array( 1, 4, 10 )
                    ),
                    new Criterion\LogicalNot(
                        new Criterion\ContentId(
                            array( 10, 12 )
                        )
                    ),
                )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\Subtree
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testContentSubtreeFilterIn()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\Subtree(
                array(
                    '/1/2/69/',
                )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 67, 68, 69, 70, 71, 72, 73, 74 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\Subtree
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testContentSubtreeFilterEq()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\Subtree(
                '/1/2/69/'
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 67, 68, 69, 70, 71, 72, 73, 74 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\ContentTypeId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testContentTypeFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\ContentTypeId(
                4
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 10, 14 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\ContentTypeGroupId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testContentTypeGroupFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\ContentTypeGroupId(
                2
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 11, 12, 13, 42, 225, 10, 14 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\DateMetadata
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testDateMetadataFilterModifiedGreater()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\DateMetadata(
                Criterion\DateMetadata::MODIFIED,
                Criterion\Operator::GT,
                1311154214
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 11, 225 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\DateMetadata
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testDateMetadataFilterModifiedGreaterOrEqual()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\DateMetadata(
                Criterion\DateMetadata::MODIFIED,
                Criterion\Operator::GTE,
                1311154214
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 11, 14, 225 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\DateMetadata
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testDateMetadataFilterModifiedIn()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\DateMetadata(
                Criterion\DateMetadata::MODIFIED,
                Criterion\Operator::IN,
                array( 1311154214, 1311154215 )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 11, 14, 225 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\DateMetadata
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testDateMetadataFilterModifiedBetween()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\DateMetadata(
                Criterion\DateMetadata::MODIFIED,
                Criterion\Operator::BETWEEN,
                array( 1311154213, 1311154215 )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 11, 14, 225 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\DateMetadata
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testDateMetadataFilterCreatedBetween()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\DateMetadata(
                Criterion\DateMetadata::CREATED,
                Criterion\Operator::BETWEEN,
                array( 1299780749, 1311154215 )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 131, 66, 225 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\LocationId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testLocationIdFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\LocationId(
                array( 1, 2, 5 )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 65 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\ParentLocationId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testParentLocationIdFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\ParentLocationId(
                array( 1 )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 41, 45, 56, 65 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\RemoteId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testRemoteIdFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\RemoteId(
                array( 'f5c88a2209584891056f987fd965b0ba', 'faaeb9be3bd98ed09f606fc16d144eca' )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 10 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\LocationRemoteId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testLocationRemoteIdFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\LocationRemoteId(
                array( '3f6d92f8044aed134f32153517850f5a', 'f3e90596361e31d496d4026eb624c983' )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 65 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\SectionId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testSectionFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\SectionId(
                array( 2 )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 42 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\Status
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testStatusFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\Status(
                array( Criterion\Status::STATUS_PUBLISHED )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 41, 42, 45, 49 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\Field
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testFieldFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\Field(
                'name',
                Criterion\Operator::EQ,
                'members'
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 11 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\Field
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testFieldFilterIn()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\Field(
                'name',
                Criterion\Operator::IN,
                array( 'members', 'anonymous users' )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 11, 42 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\Field
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testFieldFilterBetween()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\Field(
                'price',
                Criterion\Operator::BETWEEN,
                array( 10000, 1000000 )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 69, 71 ,72 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\Field
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\LogicalOr
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testFieldFilterOr()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\LogicalOr(
                array(
                    new Criterion\Field(
                        'name',
                        Criterion\Operator::EQ,
                        'members'
                    ),
                    new Criterion\Field(
                        'price',
                        Criterion\Operator::BETWEEN,
                        array( 10000, 1000000 )
                    )
                )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 11, 69, 71 ,72 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FullText
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testFullTextFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\FullText(
                'applied webpage'
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 191 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FullText
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testFullTextWildcardFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\FullText(
                'applie*'
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 191 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FullText
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testFullTextDisabledWildcardFilter()
    {
        $locator = $this->getContentSearchHandler(
            array(
                'enableWildcards' => false,
            )
        );

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\FullText(
                'applie*'
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array(),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FullText
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testFullTextFilterStopwordRemoval()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\FullText(
                'the'
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array(),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\FullText
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testFullTextFilterNoStopwordRemoval()
    {
        $locator = $this->getContentSearchHandler(
            array(
                'searchThresholdValue' => PHP_INT_MAX
            )
        );

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\FullText(
                'the'
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            10,
            count(
                array_map(
                    function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                    $result->searchHits
                )
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\ObjectStateId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testObjectStateIdFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\ObjectStateId(
                1
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 41, 42, 45, 49 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\ObjectStateId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testObjectStateIdFilterIn()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\ObjectStateId(
                array( 1, 2 )
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 41, 42, 45, 49 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\LanguageId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testLanguageIdFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\LanguageCode(
                'eng-GB'
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 41, 42, 45, 49 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\LanguageId
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testLanguageIdFilterIn()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\LanguageCode(
                'eng-US', 'eng-GB'
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 41, 42, 45, 49 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\Visibility
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testVisibilityFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent( new Query( array(
            'criterion' => new Criterion\Visibility(
                Criterion\Visibility::VISIBLE
            ),
            'limit' => 10,
        ) ) );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 41, 42, 45, 49 ),
            array_map(
                function ( $hit ) { return $hit->valueObject->versionInfo->contentInfo->id; },
                $result->searchHits
            )
        );
    }
}
