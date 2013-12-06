<?php
/**
 * File containing the SearchServiceTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\Core\Repository\SearchService;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Test case for operations in the SearchService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\SearchService
 * @group integration
 * @group search
 */
class SearchServiceSyncTest extends SearchBaseTest
{
    /**
     * Testing synchronization faults between search index and storage
     *
     * This is a complex test, which does the following:
     *
     * 1) Create new content with a unique string
     * 2) Index content in search
     * 3) Find content
     * 4) Update content object, but do not re-index
     * 5) Update content (again) based on search result
     *
     * This is supposed to test for inconsistencies between search index and
     * content storage, and if those inconsistencies are handled well. This
     * test is complex, but it is a complex use case.
     */
    public function testLoadAndUpdateOutdatedSearchDocument()
    {
        $repository = $this->getInnerRepository();

        // First create content, we can then search for:
        $contentService = $repository->getContentService();
        $contentCreate = $contentService->newContentCreateStruct(
            $repository->getContentTypeService()->loadContentTypeByIdentifier( 'forum' ),
            'eng-US'
        );
        $contentCreate->setField( 'name', 'Test content with a (hopefully) unique string: skhdfgksdfgh' );

        $contentCreate->remoteId = md5( time() );
        $contentCreate->alwaysAvailable = true;

        $content = $contentService->createContent( $contentCreate );
        $content = $contentService->publishVersion( $content->getVersionInfo() );

        // Force index content
        $persistenceHandler = $this->getPersistenceHandler();
        try {
            $persistenceHandler->searchHandler()->indexContent(
                $persistenceHandler->contentHandler()->load(
                    $content->versionInfo->contentInfo->id,
                    $content->versionInfo->contentInfo->currentVersionNo
                )
            );
        } catch ( \Exception $e ) {
            // This ignores not implemented exceptions from legacy handler, but
            // might hide valid exceptions.
        }

        // Query content
        $searchService = $repository->getSearchService();
        $result = $searchService->findContent( new Query( array(
            'filter'    => new Criterion\ContentId( $content->versionInfo->contentInfo->id ),
        ) ) );

        $this->assertEquals(
            $content->versionInfo,
            $result->searchHits[0]->valueObject->versionInfo
        );

        // Update content object
        $draft = $contentService->createContentDraft( $content->versionInfo->contentInfo );

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->initialLanguageCode = 'eng-US';
        $contentUpdate->setField( 'name', 'Test content with two (hopefully) unique strings: skhdfgksdfgh nsldfgjsdfg' );

        $draft = $contentService->updateContent(
            $draft->getVersionInfo(),
            $contentUpdate
        );

        $newContent = $contentService->publishVersion( $draft->getVersionInfo() );

        $this->assertEquals(
            2,
            $newContent->versionInfo->versionNo
        );

        // Test query for updated content
        //
        // Mind: The updated content has not been indexed yet
        $searchService = $repository->getSearchService();
        $result = $searchService->findContent( new Query( array(
            'filter'    => new Criterion\ContentId( $newContent->versionInfo->contentInfo->id ),
        ) ) );

        // The versions may actually mismatch
        $this->assertEquals(
            $newContent->versionInfo->contentInfo->id,
            $result->searchHits[0]->valueObject->versionInfo->contentInfo->id
        );

        $content = $result->searchHits[0]->valueObject;

        // Test update content which results from search
        $draft = $contentService->createContentDraft( $content->versionInfo->contentInfo );

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->initialLanguageCode = 'eng-US';
        $contentUpdate->setField( 'name', 'Test content with two (hopefully) unique strings: skhdfgksdfgh nsldfgjsdfg' );

        $draft = $contentService->updateContent(
            $draft->getVersionInfo(),
            $contentUpdate
        );

        $newContent = $contentService->publishVersion( $draft->getVersionInfo() );

        $this->assertEquals(
            3,
            $newContent->versionInfo->versionNo
        );
    }

    protected function getInnerRepository()
    {
        $repository = $this->getRepository();

        while ( property_exists( $repository, 'repository' ) )
        {
            $innerRepositoryProperty = new \ReflectionProperty( $repository, 'repository' );
            $innerRepositoryProperty->setAccessible( true );
            $repository = $innerRepositoryProperty->getValue( $repository );
        }

        return $repository;
    }

    protected function getPersistenceHandler()
    {
        $innerRepository = $this->getInnerRepository();

        $persistenceHandlerProperty = new \ReflectionProperty( $innerRepository, 'persistenceHandler' );
        $persistenceHandlerProperty->setAccessible( true );
        $persistenceHandler = $persistenceHandlerProperty->getValue( $innerRepository );

        return $persistenceHandler;
    }
}
