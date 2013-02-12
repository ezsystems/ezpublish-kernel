<?php
/**
 * File containing the BaseLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Abstract base class for limitation tests.
 *
 * @group integration
 * @group limitation
 */
abstract class BaseLimitationTest extends BaseTest
{
    /**
     * Creates a published wiki page.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createWikiPage()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();
        /* BEGIN: Inline */
        $draft = $this->createWikiPageDraft();

        $content = $contentService->publishVersion( $draft->versionInfo );
        /* END: Inline */

        return $content;
    }

    /**
     * Creates a fresh clean content draft.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createWikiPageDraft()
    {
        $repository = $this->getRepository();

        $parentLocationId = $this->generateId( 'location', 60 );
        $sectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Inline */
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        // Configure new location
        // $parentLocationId is the id of the /Home/Contact-Us node
        $locationCreate = $locationService->newLocationCreateStruct( $parentLocationId );

        $locationCreate->priority = 23;
        $locationCreate->hidden = true;
        $locationCreate->remoteId = '0123456789abcdef0123456789abcdef';
        $locationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // Load content type
        $wikiPageType = $contentTypeService->loadContentTypeByIdentifier( 'wiki_page' );

        // Configure new content object
        $wikiPageCreate = $contentService->newContentCreateStruct( $wikiPageType, 'eng-US' );

        $wikiPageCreate->setField( 'title', 'An awesome wiki page' );
        $wikiPageCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        // $sectionId is the ID of section 1
        $wikiPageCreate->sectionId = $sectionId;
        $wikiPageCreate->alwaysAvailable = true;

        // Create a draft
        $draft = $contentService->createContent(
            $wikiPageCreate,
            array( $locationCreate )
        );
        /* END: Inline */

        return $draft;
    }

    /**
     * Marks the limitation integration tests skipped against memory stub
     *
     * Since the limitations integration tests rely on multiple factors which are
     * complicated and hard to mimic by the memory stub, these should only run
     * against the real core implementation.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        if ( $this->getRepository() instanceof RepositoryStub )
        {
            $this->markTestSkipped(
                'Limitation integration tests cannot be run against memory stub.'
            );
        }
    }
}
