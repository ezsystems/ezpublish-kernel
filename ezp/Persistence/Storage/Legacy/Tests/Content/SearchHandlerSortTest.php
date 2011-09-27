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
    ezp\Persistence\Storage\Legacy\Content\Gateway\EzcDatabase\QueryBuilder,
    ezp\Persistence\Storage\Legacy\Content,
    ezp\Persistence\Content as ContentObject,
    ezp\Persistence\Content\Query\Criterion,
    ezp\Persistence\Content\Query\SortClause,
    ezp\Content\Query,
    ezp\Persistence;

/**
 * Test case for ContentSearchHandler
 */
class SearchHandlerSortTest extends TestCase
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

    /**
     * Returns the content search handler to test
     *
     * This method returns a fully functional search handler to perform tests
     * on.
     *
     * @param array $fullTextSearchConfiguration
     * @return \ezp\Persistence\Storage\Legacy\Content\Search\Handler
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

        $db = $this->getDatabaseHandler();
        return new Content\Search\Handler(
            new Content\Search\Gateway\EzcDatabase(
                $this->getDatabaseHandler(),
                new Content\Search\Gateway\CriteriaConverter(
                    array(
                        new Content\Search\Gateway\CriterionHandler\SectionId( $db ),
                    )
                ),
                new Content\Search\Gateway\SortClauseConverter(
                    array(
                        new Content\Search\Gateway\SortClauseHandler\LocationPathString( $db ),
                        new Content\Search\Gateway\SortClauseHandler\LocationDepth( $db ),
                    )
                ),
                new QueryBuilder( $this->getDatabaseHandler() )
            ),
            $this->getContentMapperMock()
        );
    }

    /**
     * Returns a content mapper mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Mapper
     */
    protected function getContentMapperMock()
    {
        $mapperMock = $this->getMock(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Mapper',
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

    public function testNoSorting()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\SectionId(
                array( 2 )
            ),
            0, 10,
            array()
        );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 42 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
            )
        );
    }

    public function testSortLocationPathString()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\SectionId(
                array( 2 )
            ),
            0, 10,
            array(
                new SortClause\LocationPathString( Query::SORT_DESC ),
            )
        );

        $this->assertEquals(
            array( 10, 42, 13, 14, 12, 11, 4 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
            )
        );
    }

    public function testSortLocationDepth()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\SectionId(
                array( 2 )
            ),
            0, 10,
            array(
                new SortClause\LocationDepth( Query::SORT_ASC ),
            )
        );

        $this->assertEquals(
            array( 4, 11, 12, 13, 42, 10, 14 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
            )
        );
    }

    public function testSortLocationDepthAndPathString()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\SectionId(
                array( 2 )
            ),
            0, 10,
            array(
                new SortClause\LocationDepth( Query::SORT_ASC ),
                new SortClause\LocationPathString( Query::SORT_DESC ),
            )
        );

        $this->assertEquals(
            array( 4, 42, 13, 12, 11, 10, 14 ),
            array_map(
                function ( $content ) { return $content->id; },
                $result->content
            )
        );
    }
}
