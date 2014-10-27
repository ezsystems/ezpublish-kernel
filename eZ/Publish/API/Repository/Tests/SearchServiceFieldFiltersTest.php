<?php
/**
 * File containing the SearchServiceFieldFiltersTest class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Tests\SetupFactory\LegacyElasticsearch;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Test case for field filtering operations in the SearchService.
 *
 * @see eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 * @group field_filter
 */
class SearchServiceFieldFiltersTest extends BaseTest
{
    public function setUp()
    {
        $setupFactory = $this->getSetupFactory();

        if ( !$setupFactory instanceof LegacyElasticsearch )
        {
            $this->markTestIncomplete( "ATM implemented only for Elasticsearch storage" );
        }

        parent::setUp();
    }

    protected function addMapLocationToFolderType()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $contentTypeDraft = $contentTypeService->createContentTypeDraft(
            $contentTypeService->loadContentTypeByIdentifier( 'folder' )
        );

        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct(
            "map_location",
            "ezgmaplocation"
        );
        $fieldDefinitionCreateStruct->names = array( "eng-GB" => "Map location field" );
        $fieldDefinitionCreateStruct->fieldGroup = "main";
        $fieldDefinitionCreateStruct->position = 1;
        $fieldDefinitionCreateStruct->isTranslatable = true;
        $fieldDefinitionCreateStruct->isSearchable = true;

        $contentTypeService->addFieldDefinition( $contentTypeDraft, $fieldDefinitionCreateStruct );

        $contentTypeService->publishContentTypeDraft( $contentTypeDraft );
    }

    /**
     * @param string $languageCode1
     * @param string $name1
     * @param string $languageCode2
     * @param string $name2
     * @param string $mainLanguageCode
     * @param boolean $alwaysAvailable
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createTestFolderWithName(
        $languageCode1,
        $name1,
        $languageCode2,
        $name2,
        $mainLanguageCode,
        $alwaysAvailable = false
    )
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        $contentCreateStruct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier( 'folder' ),
            $mainLanguageCode
        );
        $contentCreateStruct->alwaysAvailable = $alwaysAvailable;

        $contentCreateStruct->setField( "name", $name1, $languageCode1 );
        $contentCreateStruct->setField( "name", $name2, $languageCode2 );

        $content = $contentService->publishVersion(
            $contentService->createContent(
                $contentCreateStruct,
                array( $locationService->newLocationCreateStruct( 2 ) )
            )->versionInfo
        );

        return $content;
    }

    /**
     * @param string $languageCode1
     * @param string $location1
     * @param string $languageCode2
     * @param string $location2
     * @param string $mainLanguageCode
     * @param boolean $alwaysAvailable
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createTestFolderWithLocation(
        $languageCode1,
        $location1,
        $languageCode2,
        $location2,
        $mainLanguageCode,
        $alwaysAvailable = false
    )
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        $contentCreateStruct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentTypeByIdentifier( 'folder' ),
            $mainLanguageCode
        );
        $contentCreateStruct->alwaysAvailable = $alwaysAvailable;

        $contentCreateStruct->setField(
            "map_location",
            array(
                "latitude" => $location1[0],
                "longitude" => $location1[1],
                "address" => "",
            ),
            $languageCode1
        );
        $contentCreateStruct->setField(
            "map_location",
            array(
                "latitude" => $location2[0],
                "longitude" => $location2[1],
                "address" => "",
            ),
            $languageCode2
        );

        $content = $contentService->publishVersion(
            $contentService->createContent(
                $contentCreateStruct,
                array( $locationService->newLocationCreateStruct( 2 ) )
            )->versionInfo
        );

        return $content;
    }

    /**
     * Test for the findContent() method.
     *
     * Demonstrating how mismatch between field filters and language filtering criteria
     * when using non-field filtering criteria can cause NotFound exception.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testFieldFiltersCauseNotFoundException()
    {
        // Content with id=54 exists only in eng-US language!
        $query = new Query(
            array(
                "filter" => new Criterion\ContentId( 54 ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        // The content will be found, but field filtering in the service will cause the exception.
        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
        );

        $searchService->findContent( $query, $fieldFilters );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextQueryLanguageAll( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\FullText( "one" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextQueryLanguage( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\FullText( "one" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextQueryLanguageComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\FullText( "one" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content2->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextQueryLanguageEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\FullText( "one" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextQueryLanguageAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", false );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-US", true );

        $query = new Query(
            array(
                $type => new Criterion\FullText( "one" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextQueryLanguageAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB", false );

        $query = new Query(
            array(
                $type => new Criterion\FullText( "one" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextQueryAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-US", true );

        $query = new Query(
            array(
                $type => new Criterion\FullText( "one" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextQueryAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB", true );

        $query = new Query(
            array(
                $type => new Criterion\FullText( "one" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextQueryAlwaysAvailableEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-US", true );

        $query = new Query(
            array(
                $type => new Criterion\FullText( "two" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextFilterLanguageAll()
    {
        $this->testFullTextQueryLanguageAll( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextFilterLanguage()
    {
        $this->testFullTextQueryLanguage( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextFilterLanguageComplement()
    {
        $this->testFullTextQueryLanguageComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextFilterLanguageEmpty()
    {
        $this->testFullTextQueryLanguageEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextFilterLanguageAlwaysAvailable()
    {
        $this->testFullTextQueryLanguageAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextFilterLanguageAlwaysAvailableComplement()
    {
        $this->testFullTextQueryLanguageAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextFilterAlwaysAvailable()
    {
        $this->testFullTextQueryAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextFilterAlwaysAvailableComplement()
    {
        $this->testFullTextQueryAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFullTextFilterAlwaysAvailableEmpty()
    {
        $this->testFullTextQueryAlwaysAvailableEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldQueryAll( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::EQ, "two" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldQuery( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::EQ, "two" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content2->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldQueryComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::EQ, "two" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldQueryEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::EQ, "two" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldQueryLanguageAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", false );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-US", true );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::EQ, "one" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldQueryLanguageAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB", false );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::EQ, "one" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldQueryAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-US", true );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::EQ, "one" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldQueryAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB", true );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::EQ, "one" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldQueryAlwaysAvailableEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-US", true );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::EQ, "two" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeQueryAll( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::GTE, "z" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeQuery( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::GTE, "z" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content2->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeQueryComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::GTE, "z" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeQueryEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::GTE, "z" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeQueryLanguageAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB", false );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-US", true );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::GTE, "z" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_DESC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeQueryLanguageAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB", false );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::GTE, "z" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_DESC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeQueryAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-US", true );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::GTE, "z" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_DESC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeQueryAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB", true );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::GTE, "z" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeQueryAlwaysAvailableEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-US", true );

        $query = new Query(
            array(
                $type => new Criterion\Field( "name", Operator::GTE, "z" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }


    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldFilterAll()
    {
        $this->testFieldQueryAll( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldFilter()
    {
        $this->testFieldQuery( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldFilterComplement()
    {
        $this->testFieldQueryComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldFilterEmpty()
    {
        $this->testFieldQueryEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldFilterLanguageAlwaysAvailable()
    {
        $this->testFieldQueryLanguageAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldFilterLanguageAlwaysAvailableComplement()
    {
        $this->testFieldQueryLanguageAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldFilterAlwaysAvailable()
    {
        $this->testFieldQueryAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldFilterAlwaysAvailableComplement()
    {
        $this->testFieldQueryAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldFilterAlwaysAvailableEmpty()
    {
        $this->testFieldQueryAlwaysAvailableEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeFilterAll()
    {
        $this->testFieldRangeQueryAll( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeFilter()
    {
        $this->testFieldRangeQuery( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeFilterComplement()
    {
        $this->testFieldRangeQueryComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeFilterEmpty()
    {
        $this->testFieldRangeQueryEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeFilterLanguageAlwaysAvailable()
    {
        $this->testFieldRangeQueryLanguageAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeFilterLanguageAlwaysAvailableComplement()
    {
        $this->testFieldRangeQueryLanguageAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeFilterAlwaysAvailable()
    {
        $this->testFieldRangeQueryAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeFilterAlwaysAvailableComplement()
    {
        $this->testFieldRangeQueryAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testFieldRangeFilterAlwaysAvailableEmpty()
    {
        $this->testFieldRangeQueryAlwaysAvailableEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldQueryAll( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field(
                    "short_description",
                    Operator::EQ,
                    "two"
                ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldQuery( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field(
                    "short_description",
                    Operator::EQ,
                    "two"
                ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content2->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldQueryComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field(
                    "short_description",
                    Operator::EQ,
                    "two"
                ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldQueryEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field(
                    "short_description",
                    Operator::EQ,
                    "two"
                ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldQueryLanguageAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", false );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-US", true );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::EQ, "one" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldQueryLanguageAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB", false );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::EQ, "one" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldQueryAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-US", true );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::EQ, "one" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldQueryAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB", true );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::EQ, "one" ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldQueryAlwaysAvailableEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-US", true );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::EQ, "two" ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeQueryAll( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB" );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::GTE, "z" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeQuery( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB" );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::GTE, "z" ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content2->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeQueryComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB" );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::GTE, "z" ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeQueryEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB" );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::GTE, "z" ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeQueryLanguageAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB", false );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-US", true );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::GTE, "z" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_DESC, "eng-GB" ),
                ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeQueryLanguageAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB", false );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::GTE, "z" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_DESC, "eng-GB" ),
                ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeQueryAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-US", true );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::GTE, "z" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_DESC, "eng-GB" ),
                ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeQueryAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB", true );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::GTE, "z" ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeQueryAlwaysAvailableEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-US", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB", true );

        $query = new Query(
            array(
                $type => $criterion = new Criterion\Field( "short_description", Operator::GTE, "z" ),
            )
        );

        $criterion->setCustomField( "folder", "short_description", "folder_name_value_ms" );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldFilterAll()
    {
        $this->testModifiedFieldQueryAll( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldFilter()
    {
        $this->testModifiedFieldQuery( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldFilterComplement()
    {
        $this->testModifiedFieldQueryComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldFilterEmpty()
    {
        $this->testModifiedFieldQueryEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldFilterLanguageAlwaysAvailable()
    {
        $this->testModifiedFieldQueryLanguageAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldFilterLanguageAlwaysAvailableComplement()
    {
        $this->testModifiedFieldQueryLanguageAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldFilterAlwaysAvailable()
    {
        $this->testModifiedFieldQueryAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldFilterAlwaysAvailableComplement()
    {
        $this->testModifiedFieldQueryAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldFilterAlwaysAvailableEmpty()
    {
        $this->testModifiedFieldQueryAlwaysAvailableEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeFilterAll()
    {
        $this->testModifiedFieldRangeQueryAll( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeFilter()
    {
        $this->testModifiedFieldRangeQuery( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeFilterComplement()
    {
        $this->testModifiedFieldRangeQueryComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeFilterEmpty()
    {
        $this->testModifiedFieldRangeQueryEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeFilterLanguageAlwaysAvailable()
    {
        $this->testModifiedFieldRangeQueryLanguageAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeFilterLanguageAlwaysAvailableComplement()
    {
        $this->testModifiedFieldRangeQueryLanguageAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeFilterAlwaysAvailable()
    {
        $this->testModifiedFieldRangeQueryAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeFilterAlwaysAvailableComplement()
    {
        $this->testModifiedFieldRangeQueryAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testModifiedFieldRangeFilterAlwaysAvailableEmpty()
    {
        $this->testModifiedFieldRangeQueryAlwaysAvailableEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceQueryAll( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $this->addMapLocationToFolderType();

        $content1 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 20, 20 ),
            "eng-US",
            array( 30, 30 ),
            "eng-GB"
        );
        $content2 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 30, 30 ),
            "eng-US",
            array( 20, 20 ),
            "eng-GB"
        );

        $query = new Query(
            array(
                $type => new Criterion\MapLocationDistance(
                    "map_location",
                    Criterion\Operator::LTE,
                    2000,
                    10,
                    10
                ),
                'sortClauses' => array(
                    new SortClause\MapLocationDistance(
                        "folder",
                        "map_location",
                        10,
                        10,
                        Query::SORT_ASC,
                        "eng-GB"
                    ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceQuery( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $this->addMapLocationToFolderType();

        $content1 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 20, 20 ),
            "eng-US",
            array( 30, 30 ),
            "eng-GB"
        );
        $content2 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 30, 30 ),
            "eng-US",
            array( 20, 20 ),
            "eng-GB"
        );

        $query = new Query(
            array(
                $type => new Criterion\MapLocationDistance(
                    "map_location",
                    Criterion\Operator::LTE,
                    2000,
                    10,
                    10
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceQueryComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $this->addMapLocationToFolderType();

        $content1 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 20, 20 ),
            "eng-US",
            array( 30, 30 ),
            "eng-GB"
        );
        $content2 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 30, 30 ),
            "eng-US",
            array( 20, 20 ),
            "eng-GB"
        );

        $query = new Query(
            array(
                $type => new Criterion\MapLocationDistance(
                    "map_location",
                    Criterion\Operator::LTE,
                    2000,
                    10,
                    10
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content2->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceQueryEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $this->addMapLocationToFolderType();

        $content1 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 20, 20 ),
            "eng-US",
            array( 30, 30 ),
            "eng-GB"
        );
        $content2 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 30, 30 ),
            "eng-US",
            array( 20, 20 ),
            "eng-GB"
        );

        $query = new Query(
            array(
                $type => new Criterion\MapLocationDistance(
                    "map_location",
                    Criterion\Operator::LTE,
                    2000,
                    10,
                    10
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceQueryLanguageAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $this->addMapLocationToFolderType();

        $content1 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 20, 20 ),
            "eng-US",
            array( 30, 30 ),
            "eng-GB",
            false
        );
        $content2 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 30, 30 ),
            "eng-US",
            array( 20, 20 ),
            "eng-US",
            true
        );

        $query = new Query(
            array(
                $type => new Criterion\MapLocationDistance(
                    "map_location",
                    Criterion\Operator::LTE,
                    2000,
                    10,
                    10
                ),
                'sortClauses' => array(
                    new SortClause\MapLocationDistance(
                        "folder",
                        "map_location",
                        10,
                        10,
                        Query::SORT_ASC,
                        "eng-GB"
                    ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceQueryLanguageAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $this->addMapLocationToFolderType();

        $content1 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 20, 20 ),
            "eng-US",
            array( 30, 30 ),
            "eng-GB",
            true
        );
        $content2 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 30, 30 ),
            "eng-US",
            array( 20, 20 ),
            "eng-GB",
            false
        );

        $query = new Query(
            array(
                $type => new Criterion\MapLocationDistance(
                    "map_location",
                    Criterion\Operator::LTE,
                    2000,
                    10,
                    10
                ),
                'sortClauses' => array(
                    new SortClause\MapLocationDistance(
                        "folder",
                        "map_location",
                        10,
                        10,
                        Query::SORT_ASC,
                        "eng-GB"
                    ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceQueryAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $this->addMapLocationToFolderType();

        $content1 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 20, 20 ),
            "eng-US",
            array( 30, 30 ),
            "eng-GB",
            true
        );
        $content2 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 30, 30 ),
            "eng-US",
            array( 20, 20 ),
            "eng-US",
            true
        );

        $query = new Query(
            array(
                $type => new Criterion\MapLocationDistance(
                    "map_location",
                    Criterion\Operator::LTE,
                    2000,
                    10,
                    10
                ),
                'sortClauses' => array(
                    new SortClause\MapLocationDistance(
                        "folder",
                        "map_location",
                        10,
                        10,
                        Query::SORT_ASC,
                        "eng-GB"
                    ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceQueryAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $this->addMapLocationToFolderType();

        $content1 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 20, 20 ),
            "eng-US",
            array( 30, 30 ),
            "eng-GB",
            true
        );
        $content2 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 30, 30 ),
            "eng-US",
            array( 20, 20 ),
            "eng-GB",
            true
        );

        $query = new Query(
            array(
                $type => new Criterion\MapLocationDistance(
                    "map_location",
                    Criterion\Operator::LTE,
                    2000,
                    10,
                    10
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceQueryAlwaysAvailableEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $this->addMapLocationToFolderType();

        $content1 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 20, 20 ),
            "eng-US",
            array( 30, 30 ),
            "eng-US",
            true
        );
        $content2 = $this->createTestFolderWithLocation(
            "eng-GB",
            array( 30, 30 ),
            "eng-US",
            array( 20, 20 ),
            "eng-GB",
            true
        );

        $query = new Query(
            array(
                $type => new Criterion\MapLocationDistance(
                    "map_location",
                    Criterion\Operator::LTE,
                    2000,
                    10,
                    10
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceFilterAll()
    {
        $this->testMapLocationDistanceQueryAll( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceFilter()
    {
        $this->testMapLocationDistanceQuery( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceFilterComplement()
    {
        $this->testMapLocationDistanceQueryComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceFilterEmpty()
    {
        $this->testMapLocationDistanceQueryEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceFilterLanguageAlwaysAvailable()
    {
        $this->testMapLocationDistanceQueryLanguageAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceFilterLanguageAlwaysAvailableComplement()
    {
        $this->testMapLocationDistanceQueryLanguageAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceFilterAlwaysAvailable()
    {
        $this->testMapLocationDistanceQueryAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceFilterAlwaysAvailableComplement()
    {
        $this->testMapLocationDistanceQueryAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testMapLocationDistanceFilterAlwaysAvailableEmpty()
    {
        $this->testMapLocationDistanceQueryAlwaysAvailableEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldQueryAll( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\CustomField(
                    "folder_name_value_ms",
                    Operator::EQ,
                    "two"
                ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldQuery( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\CustomField(
                    "folder_name_value_ms",
                    Operator::EQ,
                    "two"
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content2->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldQueryComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\CustomField(
                    "folder_name_value_ms",
                    Operator::EQ,
                    "two"
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldQueryEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\CustomField(
                    "folder_name_value_ms",
                    Operator::EQ,
                    "two"
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldQueryLanguageAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", false );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-US", true );

        $query = new Query(
            array(
                $type => new Criterion\CustomField(
                    "folder_name_value_ms",
                    Operator::EQ,
                    "one"
                ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldQueryLanguageAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB", false );

        $query = new Query(
            array(
                $type => new Criterion\CustomField(
                    "folder_name_value_ms",
                    Operator::EQ,
                    "one"
                ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldQueryAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-US", true );

        $query = new Query(
            array(
                $type => new Criterion\CustomField(
                    "folder_name_value_ms",
                    Operator::EQ,
                    "one"
                ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldQueryAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB", true );

        $query = new Query(
            array(
                $type => new Criterion\CustomField(
                    "folder_name_value_ms",
                    Operator::EQ,
                    "one"
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldQueryAlwaysAvailableEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "one", "eng-US", "two", "eng-US", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "two", "eng-US", "one", "eng-GB", true );

        $query = new Query(
            array(
                $type => new Criterion\CustomField(
                    "folder_name_value_ms",
                    Operator::EQ,
                    "one"
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeQueryAll( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\CustomField( "folder_name_value_ms", Operator::GTE, "z" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_ASC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeQuery( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\CustomField( "folder_name_value_ms", Operator::GTE, "z" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content2->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeQueryComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\CustomField( "folder_name_value_ms", Operator::GTE, "z" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeQueryEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB" );
        $content2 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB" );

        $query = new Query(
            array(
                $type => new Criterion\CustomField( "folder_name_value_ms", Operator::GTE, "z" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeQueryLanguageAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB", false );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-US", true );

        $query = new Query(
            array(
                $type => new Criterion\CustomField( "folder_name_value_ms", Operator::GTE, "z" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_DESC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-GB",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeQueryLanguageAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB", false );

        $query = new Query(
            array(
                $type => new Criterion\CustomField( "folder_name_value_ms", Operator::GTE, "z" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_DESC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "eng-US",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeQueryAlwaysAvailable( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-US", true );

        $query = new Query(
            array(
                $type => new Criterion\CustomField( "folder_name_value_ms", Operator::GTE, "z" ),
                'sortClauses' => array(
                    new SortClause\Field( "folder", "name", Query::SORT_DESC, "eng-GB" ),
                ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 2, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
        $this->assertEquals( $content2->id, $searchResult->searchHits[1]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeQueryAlwaysAvailableComplement( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-GB", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB", true );

        $query = new Query(
            array(
                $type => new Criterion\CustomField( "folder_name_value_ms", Operator::GTE, "z" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 1, $searchResult->totalCount );
        $this->assertEquals( $content1->id, $searchResult->searchHits[0]->valueObject->id );
    }

    /**
     * Test for the findContent() method.
     *
     * @param string $type
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeQueryAlwaysAvailableEmpty( $type = null )
    {
        if ( $type === null )
        {
            $type = "query";
        }

        $content1 = $this->createTestFolderWithName( "eng-GB", "z", "eng-US", "e", "eng-US", true );
        $content2 = $this->createTestFolderWithName( "eng-GB", "e", "eng-US", "z", "eng-GB", true );

        $query = new Query(
            array(
                $type => new Criterion\CustomField( "folder_name_value_ms", Operator::GTE, "z" ),
            )
        );

        $repository = $this->getRepository();
        $searchService = $repository->getSearchService();

        $fieldFilters = array(
            "languages" => array(
                "ger-DE",
            ),
            "useAlwaysAvailable" => true,
        );

        $searchResult = $searchService->findContent( $query, $fieldFilters );

        $this->assertEquals( 0, $searchResult->totalCount );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldFilterAll()
    {
        $this->testCustomFieldQueryAll( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldFilter()
    {
        $this->testCustomFieldQuery( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldFilterComplement()
    {
        $this->testCustomFieldQueryComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldFilterEmpty()
    {
        $this->testCustomFieldQueryEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldFilterLanguageAlwaysAvailable()
    {
        $this->testCustomFieldQueryLanguageAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldFilterLanguageAlwaysAvailableComplement()
    {
        $this->testCustomFieldQueryLanguageAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldFilterAlwaysAvailable()
    {
        $this->testCustomFieldQueryAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldFilterAlwaysAvailableComplement()
    {
        $this->testCustomFieldQueryAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldFilterAlwaysAvailableEmpty()
    {
        $this->testCustomFieldQueryAlwaysAvailableEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeFilterAll()
    {
        $this->testCustomFieldRangeQueryAll( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeFilter()
    {
        $this->testCustomFieldRangeQuery( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeFilterComplement()
    {
        $this->testCustomFieldRangeQueryComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeFilterEmpty()
    {
        $this->testCustomFieldRangeQueryEmpty( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeFilterLanguageAlwaysAvailable()
    {
        $this->testCustomFieldRangeQueryLanguageAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeFilterLanguageAlwaysAvailableComplement()
    {
        $this->testCustomFieldRangeQueryLanguageAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeFilterAlwaysAvailable()
    {
        $this->testCustomFieldRangeQueryAlwaysAvailable( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeFilterAlwaysAvailableComplement()
    {
        $this->testCustomFieldRangeQueryAlwaysAvailableComplement( "filter" );
    }

    /**
     * Test for the findContent() method.
     *
     * @see \eZ\Publish\API\Repository\SearchService::findContent()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetSearchService
     */
    public function testCustomFieldRangeFilterAlwaysAvailableEmpty()
    {
        $this->testCustomFieldRangeQueryAlwaysAvailableEmpty( "filter" );
    }
}
