<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\ContentSearchHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder,
    eZ\Publish\Core\Persistence\Legacy\Content,
    eZ\Publish\Core\Persistence\Legacy\FieldHandler,
    eZ\Publish\SPI\Persistence\Content as ContentObject,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\SPI\Persistence;

/**
 * Test case for ContentSearchHandler
 */
class ContentSearchHandlerTest extends TestCase
{
    protected static $setUp = false;

    /**
     * Field registry mock
     *
     * @var \eZ\Publish\SPI\Persistence\Content\FieldValue\Converter\Registry
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
        $processor = new Content\Search\TransformationProcessor(
            new Content\Search\TransformationParser(),
            new Content\Search\TransformationPcreCompiler(
                new Content\Search\Utf8Converter()
            )
        );

        foreach ( glob( __DIR__ . '/SearchHandler/_fixtures/transformations/*.tr' ) as $file )
        {
            $processor->loadRules( $file );
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
                            $processor,
                            $fullTextSearchConfiguration
                        ),
                        new Content\Search\Gateway\CriterionHandler\Field(
                            $this->getDatabaseHandler(),
                            $this->fieldRegistry = $this->getMock(
                                '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Registry'
                            )
                        ),
                    )
                ),
                new Content\Search\Gateway\SortClauseConverter(),
                new QueryBuilder( $this->getDatabaseHandler() )
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
            array(),
            '',
            false
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
                                $contentObjs[$contentId]->id = $contentId;
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

        $result = $locator->find( new Criterion\ContentId( 10 ) );

        $this->assertEquals(
            1,
            $result->count
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

        $result = $locator->find( new Criterion\ContentId( 10 ), 0, 0 );

        $this->assertEquals(
            1,
            $result->count
        );
        $this->assertEquals(
            array(),
            $result->content
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase::find
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler::find
     */
    public function testFindWithExistingLanguageFields()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\ContentId( 11 ),
            0,
            null,
            null,
            array( 'eng-US' )
        );

        $this->assertEquals(
            1,
            $result->count
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase::find
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler::find
     */
    public function testFindWithMissingLanguageFields()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\ContentId( 4 ),
            0,
            null,
            null,
            array( 'eng-GB' )
        );

        $this->assertEquals(
            0,
            $result->count
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

        $this->assertEquals( 10, $content->id );
    }

    /**
     * @expectedException eZ\Publish\Core\Persistence\Legacy\Exception\InvalidObjectCount
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler::findSingle
     */
    public function testFindSingleTooMany()
    {
        $locator = $this->getContentSearchHandler();
        $locator->findSingle( new Criterion\ContentId( array( 4, 10, 12, 23 ) ) );
    }

    /**
     * @expectedException eZ\Publish\Core\Persistence\Legacy\Exception\InvalidObjectCount
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

        $result = $locator->find(
            new Criterion\ContentId(
                array( 1, 4, 10 )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 10 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\ContentId(
                array( 1, 4, 10 )
            ),
            0, 10, null
        );

        $this->assertSame( 2, $result->count );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\CriterionHandler\LogicalAnd
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Search\Gateway\EzcDatabase
     */
    public function testContentAndCombinatorFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\LogicalAnd(
                array(
                    new Criterion\ContentId(
                        array( 1, 4, 10 )
                    ),
                    new Criterion\ContentId(
                        array( 4, 12 )
                    ),
                )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\LogicalOr(
                array(
                    new Criterion\ContentId(
                        array( 1, 4, 10 )
                    ),
                    new Criterion\ContentId(
                        array( 4, 12 )
                    ),
                )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 10, 12 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\LogicalAnd(
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
            0, 10, null
        );

        $this->assertEquals(
            array( 4 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\Subtree(
                array(
                    '/1/2/69/',
                )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 67, 68, 69, 70, 71, 72, 73, 74 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\Subtree(
                '/1/2/69/'
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 67, 68, 69, 70, 71, 72, 73, 74 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\ContentTypeId(
                4
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 10, 14 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\ContentTypeGroupId(
                2
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 11, 12, 13, 42, 225, 10, 14 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\DateMetadata(
                Criterion\DateMetadata::MODIFIED,
                Criterion\Operator::GT,
                1311154214
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 11, 225 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\DateMetadata(
                Criterion\DateMetadata::MODIFIED,
                Criterion\Operator::GTE,
                1311154214
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 11, 14, 225 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\DateMetadata(
                Criterion\DateMetadata::MODIFIED,
                Criterion\Operator::IN,
                array( 1311154214, 1311154215 )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 11, 14, 225 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\DateMetadata(
                Criterion\DateMetadata::MODIFIED,
                Criterion\Operator::BETWEEN,
                array( 1311154213, 1311154215 )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 11, 14, 225 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\DateMetadata(
                Criterion\DateMetadata::CREATED,
                Criterion\Operator::BETWEEN,
                array( 1299780749, 1311154215 )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 131, 66, 225 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\LocationId(
                array( 1, 2, 5 )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 65 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\ParentLocationId(
                array( 1 )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 41, 45, 56, 65 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\RemoteId(
                array( 'f5c88a2209584891056f987fd965b0ba', 'faaeb9be3bd98ed09f606fc16d144eca' )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 10 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\LocationRemoteId(
                array( '3f6d92f8044aed134f32153517850f5a', 'f3e90596361e31d496d4026eb624c983' )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 65 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\SectionId(
                array( 2 )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 42 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\Status(
                array( Criterion\Status::STATUS_PUBLISHED )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 41, 42, 45, 49 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $converter = $this->getMock( '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter' );
        $converter
            ->expects( $this->once() )
            ->method( 'getIndexColumn' )
            ->will( $this->returnValue( 'sort_key_string' ) );

        $this->fieldRegistry
            ->expects( $this->once() )
            ->method( 'getConverter' )
            ->with( 'ezstring' )
            ->will( $this->returnValue( $converter ) );

        $result = $locator->find(
            new Criterion\Field(
                'name',
                Criterion\Operator::EQ,
                'members'
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 11 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $converter = $this->getMock( '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter' );
        $converter
            ->expects( $this->once() )
            ->method( 'getIndexColumn' )
            ->will( $this->returnValue( 'sort_key_string' ) );

        $this->fieldRegistry
            ->expects( $this->once() )
            ->method( 'getConverter' )
            ->with( 'ezstring' )
            ->will( $this->returnValue( $converter ) );

        $result = $locator->find(
            new Criterion\Field(
                'name',
                Criterion\Operator::IN,
                array( 'members', 'anonymous users' )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 11, 42 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $converter = $this->getMock( '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter' );
        $converter
            ->expects( $this->once() )
            ->method( 'getIndexColumn' )
            ->will( $this->returnValue( 'sort_key_int' ) );

        $this->fieldRegistry
            ->expects( $this->once() )
            ->method( 'getConverter' )
            ->with( 'ezprice' )
            ->will( $this->returnValue( $converter ) );

        $result = $locator->find(
            new Criterion\Field(
                'price',
                Criterion\Operator::BETWEEN,
                array( 10000, 1000000 )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 69, 71 ,72 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $converter = $this->getMock( '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter' );
        $converter
            ->expects( $this->at( 0 ) )
            ->method( 'getIndexColumn' )
            ->will( $this->returnValue( 'sort_key_string' ) );

        $converter
            ->expects( $this->at( 1 ) )
            ->method( 'getIndexColumn' )
            ->will( $this->returnValue( 'sort_key_int' ) );

        $this->fieldRegistry
            ->expects( $this->at( 0 ) )
            ->method( 'getConverter' )
            ->with( 'ezstring' )
            ->will( $this->returnValue( $converter ) );

        $this->fieldRegistry
            ->expects( $this->at( 1 ) )
            ->method( 'getConverter' )
            ->with( 'ezprice' )
            ->will( $this->returnValue( $converter ) );

        $result = $locator->find(
            new Criterion\LogicalOr(
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
            0, 10, null
        );

        $this->assertEquals(
            array( 11, 69, 71 ,72 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\FullText(
                'applied webpage'
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 191 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\FullText(
                'applie*'
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 191 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\FullText(
                'applie*'
            ),
            0, 10, null
        );

        $this->assertEquals(
            array(),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\FullText(
                'the'
            ),
            0, 10, null
        );

        $this->assertEquals(
            array(),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
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

        $result = $locator->find(
            new Criterion\FullText(
                'the'
            ),
            0, 10, null
        );

        $this->assertEquals(
            10,
            count(
                array_map(
                    function ( $content )
                    {
                        return $content->id;
                    },
                    $result->content
                )
            )
        );
    }
}
