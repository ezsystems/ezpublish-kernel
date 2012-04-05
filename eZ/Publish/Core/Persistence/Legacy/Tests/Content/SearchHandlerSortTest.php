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
                        new Content\Search\Gateway\SortClauseHandler\ContentName( $db ),
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
                                $contentObjs[$contentId]->contentInfo->contentId = $contentId;
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
                    return $content->contentInfo->contentId;
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
                    return $content->contentInfo->contentId;
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
                    return $content->contentInfo->contentId;
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
                    return $content->contentInfo->contentId;
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
                    return $content->contentInfo->contentId;
                },
                $result->content
            )
        );
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
                    return $content->contentInfo->contentId;
                },
                $result->content
            )
        );
    }
}
