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
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator,
    eZ\Publish\SPI\Persistence\Content as ContentObject,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion,
    eZ\Publish\API\Repository\Values\Content\Query\SortClause,
    eZ\Publish\SPI\Persistence\Content\Language,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\API\Repository\Values\Content\Query,
    eZ\Publish\SPI\Persistence;

/**
 * Test case for ContentSearchHandler
 */
class SearchHandlerSortTest extends LanguageAwareTestCase
{
    protected static $setUp = false;

    /**
     * Field registry mock
     *
     * @var \eZ\Publish\SPI\Persistence\Content\FieldValue\Converter\Registry
     */
    protected $fieldRegistry;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingLanguageHandler
     */
    protected $languageHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

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
                $this->getLanguageHandlerMock(),
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
                $this->locationMapperMock = $this->getMock(
                    'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Mapper',
                    array(),
                    array(),
                    '',
                    false
                ),
                $this->getFieldRegistry(),
                $this->getLanguageHandlerMock()
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
                                $contentObjs[$contentId]->contentInfo = new ContentInfo;
                                $contentObjs[$contentId]->contentInfo->id = $contentId;
                            }
                        }
                        return array_values( $contentObjs );
                    }
                )
            );
        return $mapperMock;
    }

    /**
     * Returns a language handler mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingLanguageHandler
     */
    protected function getLanguageHandlerMock()
    {
        if ( !isset( $this->languageHandler ) )
        {
            $innerLanguageHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );
            $innerLanguageHandler->expects( $this->any() )
                ->method( 'loadAll' )
                ->will(
                    $this->returnValue(
                        array(
                            new Language( array(
                                'id'            => 2,
                                'languageCode'  => 'eng-GB',
                                'name'          => 'British english'
                            ) ),
                            new Language( array(
                                'id'            => 4,
                                'languageCode'  => 'eng-US',
                                'name'          => 'US english'
                            ) ),
                            new Language( array(
                                'id'            => 8,
                                'languageCode'  => 'fre-FR',
                                'name'          => 'Français franchouillard'
                            ) )
                        )
                    )
                );
            $this->languageHandler = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\CachingHandler',
                array( 'getByLocale', 'getById' ),
                array(
                    $innerLanguageHandler,
                    $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\Cache' )
                )
            );
            $this->languageHandler->expects( $this->any() )
                ->method( 'getById' )
                ->will(
                    $this->returnValue(
                        new Language(
                            array(
                                'id'            => 2,
                                'languageCode'  => 'eng-GB',
                                'name'          => 'British english'
                            )
                        )
                    )
                );
        }
        return $this->languageHandler;
    }

    /**
     * Returns a field registry mock object
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue\Converter\Registry
     */
    protected function getFieldRegistry()
    {
        if ( !isset( $this->fieldRegistry ) )
        {
            $this->fieldRegistry = $this->getMock(
                '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldValue\\Converter\\Registry'
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

    /**
     * Returns a language mask generator
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected function getLanguageMaskGenerator()
    {
        if ( !isset( $this->languageMaskGenerator ) )
        {
            $this->languageMaskGenerator = new LanguageMaskGenerator(
                $this->getLanguageLookupMock()
            );
        }
        return $this->languageMaskGenerator;
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
                function ( $content )
                {
                    return $content->contentInfo->id;
                },
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
                function ( $content )
                {
                    return $content->contentInfo->id;
                },
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
                function ( $content )
                {
                    return $content->contentInfo->id;
                },
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
                function ( $content )
                {
                    return $content->contentInfo->id;
                },
                $result->content
            )
        );
    }

    public function testSortLocationPriority()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\SectionId(
                array( 2 )
            ),
            0, 10,
            array(
                new SortClause\LocationPriority( Query::SORT_DESC ),
            )
        );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 42 ),
            array_map(
                function ( $content )
                {
                    return $content->contentInfo->id;
                },
                $result->content
            )
        );
    }

    public function testSortDateModified()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\SectionId(
                array( 2 )
            ),
            0, 10,
            array(
                new SortClause\DateModified(),
            )
        );

        $this->assertEquals(
            array( 4, 12, 13, 42, 10, 14, 11 ),
            array_map(
                function ( $content )
                {
                    return $content->contentInfo->id;
                },
                $result->content
            )
        );
    }

    public function testSortDatePublished()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\SectionId(
                array( 2 )
            ),
            0, 10,
            array(
                new SortClause\DatePublished(),
            )
        );

        $this->assertEquals(
            array( 4, 10, 11, 12, 13, 14, 42 ),
            array_map(
                function ( $content )
                {
                    return $content->contentInfo->id;
                },
                $result->content
            )
        );
    }

    public function testSortSectionIdentifier()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\SectionId(
                array( 4, 2, 6, 3 )
            ),
            0, null,
            array(
                new SortClause\SectionIdentifier(),
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
            function ( $content )
            {
                return $content->contentInfo->id;
            },
            $result->content
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

        $result = $locator->find(
            new Criterion\SectionId(
                array( 4, 2, 6, 3 )
            ),
            0, null,
            array(
                new SortClause\SectionName(),
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
            function ( $content )
            {
                return $content->contentInfo->id;
            },
            $result->content
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

        $result = $locator->find(
            new Criterion\SectionId(
                array( 2, 3 )
            ),
            0, null,
            array(
                new SortClause\ContentName(),
            )
        );

        $this->assertEquals(
            array( 14, 12, 10, 42, 57, 13, 50, 49, 41, 11, 51, 62, 4, 58, 59, 61, 60, 64, 63, 200, 66, 201 ),
            array_map(
                function ( $content )
                {
                    return $content->contentInfo->id;
                },
                $result->content
            )
        );
    }

    public function testSortFieldText()
    {
        $locator = $this->getContentSearchHandler();

        $result = $locator->find(
            new Criterion\SectionId(
                array( 1 )
            ),
            0, null,
            array(
                new SortClause\Field( "article", "title" ),
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
            function ( $content )
            {
                return $content->contentInfo->id;
            },
            $result->content
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

        $result = $locator->find(
            new Criterion\SectionId(
                array( 1 )
            ),
            0, null,
            array(
                new SortClause\Field( "product", "price" ),
            )
        );

        $this->assertEquals(
            array( 73, 71, 72, 69 ),
            array_map(
                function ( $content )
                {
                    return $content->contentInfo->id;
                },
                $result->content
            )
        );
    }
}
