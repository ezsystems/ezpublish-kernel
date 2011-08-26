<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\ContentSearchHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content,
    ezp\Persistence\Content\Criterion,
    ezp\Persistence;

/**
 * Test case for ContentSearchHandler
 */
class ContentSearchHandlerTest extends TestCase
{
    protected static $setUp = false;

    /**
     * Field registry mock
     *
     * @var \ezp\Persistence\Content\FieldValue\Converter\Registry
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

    protected function getContentSearchHandler( array $fullTextSearchConfiguration = array() )
    {
        return new Content\Search\Handler(
            new Content\Search\Gateway\EzcDatabase(
                $this->getDatabaseHandler(),
                new Content\Search\Gateway\CriteriaConverter( array(
                    new Content\Search\Gateway\CriterionHandler\ContentId(),
                    new Content\Search\Gateway\CriterionHandler\LogicalNot(),
                    new Content\Search\Gateway\CriterionHandler\LogicalAnd(),
                    new Content\Search\Gateway\CriterionHandler\LogicalOr(),
                    new Content\Search\Gateway\CriterionHandler\SubtreeId(),
                    new Content\Search\Gateway\CriterionHandler\ContentTypeId(),
                    new Content\Search\Gateway\CriterionHandler\ContentTypeGroupId(),
                    new Content\Search\Gateway\CriterionHandler\DateMetadata(),
                    new Content\Search\Gateway\CriterionHandler\LocationId(),
                    new Content\Search\Gateway\CriterionHandler\ParentLocationId(),
                    new Content\Search\Gateway\CriterionHandler\RemoteId(),
                    new Content\Search\Gateway\CriterionHandler\SectionId(),
                    new Content\Search\Gateway\CriterionHandler\Status(),
                    new Content\Search\Gateway\CriterionHandler\FullText(
                        $fullTextSearchConfiguration
                    ),
                    new Content\Search\Gateway\CriterionHandler\Field(
                        $this->getDatabaseHandler(),
                        $this->fieldRegistry = $this->getMock(
                            '\\ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter\\Registry'
                        )
                    ),
                ) )
            )
        );
    }

    public function testFindSingle()
    {
        $locator = $this->getContentSearchHandler();

        $content = $locator->findSingle( new Criterion\ContentId( 10 ) );

        $this->assertEquals( 10, $content->id );
    }

    /**
     * @expectedException ezp\Persistence\Storage\Legacy\Exception\InvalidObjectCount
     */
    public function testFindSingleTooMany()
    {
        $locator = $this->getContentSearchHandler();
        $locator->findSingle( new Criterion\ContentId( array( 4, 10, 12, 23 ) ) );
    }

    /**
     * @expectedException ezp\Persistence\Storage\Legacy\Exception\InvalidObjectCount
     */
    public function testFindSingleZero()
    {
        $locator = $this->getContentSearchHandler();
        $locator->findSingle( new Criterion\ContentId( 0 ) );
    }

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

    public function testContentAndCombinatorFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\LogicalAnd( array(
                new Criterion\ContentId(
                    array( 1, 4, 10 )
                ),
                new Criterion\ContentId(
                    array( 4, 12 )
                ),
            ) ),
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

    public function testContentOrCombinatorFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\LogicalOr( array(
                new Criterion\ContentId(
                    array( 1, 4, 10 )
                ),
                new Criterion\ContentId(
                    array( 4, 12 )
                ),
            ) ),
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

    public function testContentNotCombinatorFilter()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\LogicalAnd( array(
                new Criterion\ContentId(
                    array( 1, 4, 10 )
                ),
                new Criterion\LogicalNot( array(
                    new Criterion\ContentId(
                        array( 10, 12 )
                    ),
                ) ),
            ) ),
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

    public function testContentSubtreeIdFilterIn()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\SubtreeId(
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

    public function testContentSubtreeIdFilterEq()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\SubtreeId(
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

    public function testFieldFilter()
    {
        $locator = $this->getContentSearchHandler();

        $converter = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter' );
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
                new Criterion\FieldIdentifierStruct( 'user_group', 'name' ),
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

    public function testFieldFilterIn()
    {
        $locator = $this->getContentSearchHandler();

        $converter = $this->getMock( '\\ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter' );
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
                new Criterion\FieldIdentifierStruct( 'user_group', 'name' ),
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

    public function testFullTextDisabledWildcardFilter()
    {
        $locator = $this->getContentSearchHandler( array(
            'enableWildcards' => false,
        ) );

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

    public function testFullTextFilterNoStopwordRemoval()
    {
        $locator = $this->getContentSearchHandler( array(
            'searchThresholdValue' => PHP_INT_MAX
        ) );

        $result = $locator->find(
            new Criterion\FullText(
                'the'
            ),
            0, 10, null
        );

        $this->assertEquals(
            10,
            count( array_map(
                function ( $content ) { return $content->id; },
                $result->content
            ) )
        );
    }
}
