<?php

/**
 * File containing the BaseLimitationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Tests\BaseTest;
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

        $content = $contentService->publishVersion($draft->versionInfo);
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

        $parentLocationId = $this->generateId('location', 60);
        $sectionId = $this->generateId('section', 1);
        /* BEGIN: Inline */
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();

        // Configure new location
        // $parentLocationId is the id of the /Home/Contact-Us node
        $locationCreate = $locationService->newLocationCreateStruct($parentLocationId);

        $locationCreate->priority = 23;
        $locationCreate->hidden = true;
        $locationCreate->remoteId = '0123456789abcdef0123456789abcdef';
        $locationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // Load content type
        $wikiPageType = $contentTypeService->loadContentTypeByIdentifier('wiki_page');

        // Configure new content object
        $wikiPageCreate = $contentService->newContentCreateStruct($wikiPageType, 'eng-US');

        $wikiPageCreate->setField('title', 'An awesome wiki page');
        $wikiPageCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        // $sectionId is the ID of section 1
        $wikiPageCreate->sectionId = $sectionId;
        $wikiPageCreate->alwaysAvailable = true;

        // Create a draft
        $draft = $contentService->createContent(
            $wikiPageCreate,
            [$locationCreate]
        );
        /* END: Inline */

        return $draft;
    }
}
