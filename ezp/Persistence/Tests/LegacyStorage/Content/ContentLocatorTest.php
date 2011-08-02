<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\ContentLocatorTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegacyStorage\Content;
use ezp\Persistence\Tests\LegacyStorage\TestCase,
    ezp\Persistence\LegacyStorage\Content,
    ezp\Persistence\Content\Criterion,
    ezp\Persistence;

/**
 * Test case for ContentLocator
 */
class ContentLocatorTest extends TestCase
{
    protected static $setUp = false;

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
            $this->insertDatabaseFixture( __DIR__ . '/ContentLocator/_fixtures/full_dump.php' );
            self::$setUp = $this->handler;
        }
        else
        {
            $this->handler = self::$setUp;
        }
    }

    protected function getContentLocator()
    {
        return new Content\ContentLocator(
            new Content\ContentLocatorGateway\EzcDatabase(
                $this->getDatabaseHandler()
            )
        );
    }

    public function testContentIdFilter()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\ContentId(
                null,
                Criterion\Operator::IN,
                array( 1, 4, 10 )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 10 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result
            )
        );
    }

    public function testContentAndCombinatorFilter()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\LogicalAnd( array(
                new Criterion\ContentId(
                    null,
                    Criterion\Operator::IN,
                    array( 1, 4, 10 )
                ),
                new Criterion\ContentId(
                    null,
                    Criterion\Operator::IN,
                    array( 4, 12 )
                ),
            ) ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result
            )
        );
    }

    public function testContentOrCombinatorFilter()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\LogicalOr( array(
                new Criterion\ContentId(
                    null,
                    Criterion\Operator::IN,
                    array( 1, 4, 10 )
                ),
                new Criterion\ContentId(
                    null,
                    Criterion\Operator::IN,
                    array( 4, 12 )
                ),
            ) ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 10, 12 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result
            )
        );
    }

    public function testContentNotCombinatorFilter()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\LogicalAnd( array(
                new Criterion\ContentId(
                    null,
                    Criterion\Operator::IN,
                    array( 1, 4, 10 )
                ),
                new Criterion\LogicalNot( array(
                    new Criterion\ContentId(
                        null,
                        Criterion\Operator::IN,
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
                $result
            )
        );
    }

    public function testContentSubtreeFilterIn()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\Subtree(
                null,
                Criterion\Operator::IN,
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
                $result
            )
        );
    }

    public function testContentSubtreeFilterEq()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\Subtree(
                null,
                Criterion\Operator::EQ,
                '/1/2/69/'
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 67, 68, 69, 70, 71, 72, 73, 74 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result
            )
        );
    }

    public function testContentTypeFilter()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\ContentType(
                null,
                Criterion\Operator::EQ,
                4
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 10, 14 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result
            )
        );
    }

    public function testContentTypeGroupFilter()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\ContentTypeGroup(
                null,
                Criterion\Operator::EQ,
                2
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 11, 12, 13, 42, 225, 10, 14 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result
            )
        );
    }

    public function testDateMetadataFilterModifiedGreater()
    {
        $locator = $this->getContentLocator();

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
                $result
            )
        );
    }

    public function testDateMetadataFilterModifiedGreaterOrEqual()
    {
        $locator = $this->getContentLocator();

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
                $result
            )
        );
    }

    public function testDateMetadataFilterModifiedIn()
    {
        $locator = $this->getContentLocator();

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
                $result
            )
        );
    }

    public function testDateMetadataFilterModifiedBetween()
    {
        $locator = $this->getContentLocator();

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
                $result
            )
        );
    }

    public function testDateMetadataFilterCreatedBetween()
    {
        $locator = $this->getContentLocator();

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
                $result
            )
        );
    }

    public function testLocationIdFilter()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\LocationId(
                null,
                Criterion\Operator::IN,
                array( 1, 2, 5 )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 65 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result
            )
        );
    }

    public function testParentLocationIdFilter()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\ParentLocationId(
                null,
                Criterion\Operator::IN,
                array( 1 )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 41, 45, 56, 65 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result
            )
        );
    }

    public function testRemoteIdFilter()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\RemoteId(
                null,
                Criterion\Operator::IN,
                array( 'f5c88a2209584891056f987fd965b0ba', 'faaeb9be3bd98ed09f606fc16d144eca' )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 10 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result
            )
        );
    }

    public function testSectionFilter()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\Section(
                null,
                Criterion\Operator::IN,
                array( 2 )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 42 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result
            )
        );
    }

    public function testStatusFilter()
    {
        $locator = $this->getContentLocator();

        $result = $locator->find(
            new Criterion\Status(
                null,
                Criterion\Operator::IN,
                array( Criterion\Status::STATUS_PUBLISHED )
            ),
            0, 10, null
        );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 41, 42, 45, 49 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result
            )
        );
    }
}
