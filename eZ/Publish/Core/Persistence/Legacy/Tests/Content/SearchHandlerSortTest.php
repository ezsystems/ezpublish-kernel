<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\SearchHandlerSortTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Content\Gateway\EzcDatabase\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content;
use eZ\Publish\SPI\Persistence\Content as ContentObject;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * Test case for ContentSearchHandler
 */
class SearchHandlerSortTest extends LanguageAwareTestCase
{
    protected static $setUp = false;

    /**
     * Field registry mock
     *
     * @var \eZ\Publish\SPI\Persistence\Content\FieldValue\ConverterRegistry
     */
    protected $fieldRegistry;

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
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Search\Handler
     */
    protected function getContentSearchHandler( array $fullTextSearchConfiguration = array() )
    {
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
                        new Content\Search\Gateway\SortClauseHandler\LocationPriority( $db ),
                        new Content\Search\Gateway\SortClauseHandler\DateModified( $db ),
                        new Content\Search\Gateway\SortClauseHandler\DatePublished( $db ),
                        new Content\Search\Gateway\SortClauseHandler\SectionIdentifier( $db ),
                        new Content\Search\Gateway\SortClauseHandler\SectionName( $db ),
                        new Content\Search\Gateway\SortClauseHandler\ContentName( $db ),
                        new Content\Search\Gateway\SortClauseHandler\Field( $db ),
                    )
                ),
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
                $this->getFieldRegistry(),
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
     * Returns a field registry mock object
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\ConverterRegistry
     */
    protected function getFieldRegistry()
    {
        if ( !isset( $this->fieldRegistry ) )
        {
            $this->fieldRegistry = $this->getMock(
                '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\ConverterRegistry',
                array(),
                array( array() )
            );
        }
        return $this->fieldRegistry;
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

    public function testNoSorting()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'criterion'   => new Criterion\SectionId( array( 2 ) ),
                    'offset'      => 0,
                    'limit'       => 10,
                    'sortClauses' => array()
                )
            )
        );

        $ids = array_map(
            function ( $hit )
            {
                return $hit->valueObject->versionInfo->contentInfo->id;
            },
            $result->searchHits
        );
        sort( $ids );
        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 42 ),
            $ids
        );
    }

    public function testSortLocationPathString()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'criterion'   => new Criterion\SectionId( array( 2 ) ),
                    'offset'      => 0,
                    'limit'       => 10,
                    'sortClauses' => array( new SortClause\LocationPathString( Query::SORT_DESC ) )
                )
            )
        );

        $this->assertEquals(
            array( 10, 42, 13, 14, 12, 11, 4 ),
            array_map(
                function ( $hit )
                {
                    return $hit->valueObject->versionInfo->contentInfo->id;
                },
                $result->searchHits
            )
        );
    }

    public function testSortLocationDepth()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'criterion'   => new Criterion\SectionId( array( 2 ) ),
                    'offset'      => 0,
                    'limit'       => 10,
                    'sortClauses' => array( new SortClause\LocationDepth( Query::SORT_ASC ) )
                )
            )
        );

        $ids = array_map(
            function ( $hit )
            {
                return $hit->valueObject->versionInfo->contentInfo->id;
            },
            $result->searchHits
        );

        // Content with id 4 is the only one with depth = 1
        $this->assertEquals( 4, $ids[0] );

        // Content with ids 11, 12, 13, 42 are the ones with depth = 2
        $nextIds = array_slice( $ids, 1, 4 );
        sort( $nextIds );
        $this->assertEquals(
            array( 11, 12, 13, 42 ),
            $nextIds
        );

        // Content with ids 10, 14 are the ones with depth = 3
        $nextIds = array_slice( $ids, 5 );
        sort( $nextIds );
        $this->assertEquals(
            array( 10, 14 ),
            $nextIds
        );
    }

    public function testSortLocationDepthAndPathString()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'criterion'   => new Criterion\SectionId( array( 2 ) ),
                    'offset'      => 0,
                    'limit'       => 10,
                    'sortClauses' => array(
                        new SortClause\LocationDepth( Query::SORT_ASC ),
                        new SortClause\LocationPathString( Query::SORT_DESC ),
                    )
                )
            )
        );

        $this->assertEquals(
            array( 4, 42, 13, 12, 11, 10, 14 ),
            array_map(
                function ( $hit )
                {
                    return $hit->valueObject->versionInfo->contentInfo->id;
                },
                $result->searchHits
            )
        );
    }

    public function testSortLocationPriority()
    {
        // @todo FIXME: This test doesn't ensure order is correct since they all have a priority of 0.
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'criterion'   => new Criterion\SectionId( array( 2 ) ),
                    'offset'      => 0,
                    'limit'       => 10,
                    'sortClauses' => array(
                        new SortClause\LocationPriority( Query::SORT_DESC ),
                    )
                )
            )
        );

        $ids = array_map(
            function ( $hit )
            {
                return $hit->valueObject->versionInfo->contentInfo->id;
            },
            $result->searchHits
        );
        sort( $ids );
        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 42 ),
            $ids
        );
    }

    public function testSortDateModified()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->findContent(
            new Query(
                array(
                    'criterion'   => new Criterion\SectionId( array( 2 ) ),
                    'offset'      => 0,
                    'limit'       => 10,
                    'sortClauses' => array(
                        new SortClause\DateModified(),
                    )
                )
            )
        );

        $this->assertEquals(
            array( 4, 12, 13, 42, 10, 14, 11 ),
            array_map(
                function ( $hit )
                {
                    return $hit->valueObject->versionInfo->contentInfo->id;
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
                    'criterion'   => new Criterion\SectionId( array( 2 ) ),
                    'offset'      => 0,
                    'limit'       => 10,
                    'sortClauses' => array(
                        new SortClause\DatePublished(),
                    )
                )
            )
        );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 42 ),
            array_map(
                function ( $hit )
                {
                    return $hit->valueObject->versionInfo->contentInfo->id;
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
                    'criterion'   => new Criterion\SectionId( array( 4, 2, 6, 3 ) ),
                    'offset'      => 0,
                    'limit'       => null,
                    'sortClauses' => array(
                        new SortClause\SectionIdentifier(),
                    )
                )
            )
        );

        // First, results of section 2 should appear, then the ones of 3, 4 and 6
        // From inside a specific section, no particular order should be defined
        // the logic is then to have a set of sorted id's to compare with
        // the comparison being done slice by slice.
        $idMapSet = array(
            2 => array( 4, 10, 11, 12, 13, 14, 42 ),
            3 => array( 41, 49, 50, 51, 57, 58, 59, 60, 61, 62, 63, 64, 66, 200, 201 ),
            4 => array( 45, 52 ),
            6 => array( 154, 155, 156, 157, 158, 159, 160, 161, 162, 163, 164 ),
        );
        $contentIds = array_map(
            function ( $hit )
            {
                return $hit->valueObject->versionInfo->contentInfo->id;
            },
            $result->searchHits
        );
        $index = 0;

        foreach ( $idMapSet as $idSet )
        {
            $contentIdsSubset = array_slice( $contentIds, $index, $count = count( $idSet ) );
            $index += $count;
            sort( $contentIdsSubset );
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
                    'criterion'   => new Criterion\SectionId( array( 4, 2, 6, 3 ) ),
                    'offset'      => 0,
                    'limit'       => null,
                    'sortClauses' => array(
                        new SortClause\SectionName(),
                    )
                )
            )
        );

        // First, results of section "Media" should appear, then the ones of "Protected",
        // "Setup" and "Users"
        // From inside a specific section, no particular order should be defined
        // the logic is then to have a set of sorted id's to compare with
        // the comparison being done slice by slice.
        $idMapSet = array(
            "media" => array( 41, 49, 50, 51, 57, 58, 59, 60, 61, 62, 63, 64, 66, 200, 201 ),
            "protected" => array( 154, 155, 156, 157, 158, 159, 160, 161, 162, 163, 164 ),
            "setup" => array( 45, 52 ),
            "users" => array( 4, 10, 11, 12, 13, 14, 42 ),
        );
        $contentIds = array_map(
            function ( $hit )
            {
                return $hit->valueObject->versionInfo->contentInfo->id;
            },
            $result->searchHits
        );
        $index = 0;

        foreach ( $idMapSet as $idSet )
        {
            $contentIdsSubset = array_slice( $contentIds, $index, $count = count( $idSet ) );
            $index += $count;
            sort( $contentIdsSubset );
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
                    'criterion'   => new Criterion\SectionId( array( 2, 3 ) ),
                    'offset'      => 0,
                    'limit'       => null,
                    'sortClauses' => array(
                        new SortClause\ContentName(),
                    )
                )
            )
        );

        $this->assertEquals(
            array( 14, 12, 10, 42, 57, 13, 50, 49, 41, 11, 51, 62, 4, 58, 59, 61, 60, 64, 63, 200, 66, 201 ),
            array_map(
                function ( $hit )
                {
                    return $hit->valueObject->versionInfo->contentInfo->id;
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
                    'criterion'   => new Criterion\SectionId( array( 1 ) ),
                    'offset'      => 0,
                    'limit'       => null,
                    'sortClauses' => array(
                        new SortClause\Field( "article", "title" ),
                    )
                )
            )
        );

        // There are several identical titles, need to take care about this
        $idMapSet = array(
            "aenean malesuada ligula" => array( 83 ),
            "aliquam pulvinar suscipit tellus" => array( 102 ),
            "asynchronous publishing" => array( 148, 215 ),
            "canonical links" => array( 147, 216 ),
            "class aptent taciti" => array( 88 ),
            "class aptent taciti sociosqu" => array( 82 ),
            "duis auctor vehicula erat" => array( 89 ),
            "etiam posuere sodales arcu" => array( 78 ),
            "etiam sodales mauris" => array( 87 ),
            "ez publish enterprise" => array( 151 ),
            "fastcgi" => array( 144, 218 ),
            "fusce sagittis sagittis" => array( 77 ),
            "fusce sagittis sagittis urna" => array( 81 ),
            "get involved" => array( 107 ),
            "how to develop with ez publish" => array( 127, 211 ),
            "how to manage ez publish" => array( 118, 202 ),
            "how to use ez publish" => array( 108, 193 ),
            "improved block editing" => array( 136 ),
            "improved front-end editing" => array( 139 ),
            "improved user registration workflow" => array( 132 ),
            "in hac habitasse platea" => array( 79 ),
            "lots of websites, one ez publish installation" => array( 130 ),
            "rest api interface" => array( 150, 214 ),
            "separate content & design in ez publish" => array( 191 ),
            "support for red hat enterprise" => array( 145, 217 ),
            "tutorials for" => array( 106 ),
        );
        $contentIds = array_map(
            function ( $hit )
            {
                return $hit->valueObject->versionInfo->contentInfo->id;
            },
            $result->searchHits
        );
        $index = 0;

        foreach ( $idMapSet as $idSet )
        {
            $contentIdsSubset = array_slice( $contentIds, $index, $count = count( $idSet ) );
            $index += $count;
            sort( $contentIdsSubset );
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
                    'criterion'   => new Criterion\SectionId( array( 1 ) ),
                    'offset'      => 0,
                    'limit'       => null,
                    'sortClauses' => array(
                        new SortClause\Field( "product", "price" ),
                    )
                )
            )
        );

        $this->assertEquals(
            array( 73, 71, 72, 69 ),
            array_map(
                function ( $hit )
                {
                    return $hit->valueObject->versionInfo->contentInfo->id;
                },
                $result->searchHits
            )
        );
    }
}
