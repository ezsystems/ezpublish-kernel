<?php
/**
 * File containing the BaseContentServiceTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Base class for content specific tests.
 */
abstract class BaseContentServiceTest extends BaseTest
{
    /**
     * Creates a fresh clean content draft.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createContentDraftVersion1()
    {
        $repository = $this->getRepository();

        $parentLocationId = $this->generateId( 'location', 56 );
        $sectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Inline */
        // $parentLocationId is the id of the /Design/eZ-publish node

        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $locationService = $repository->getLocationService();

        // Configure new location
        $locationCreate = $locationService->newLocationCreateStruct( $parentLocationId );

        $locationCreate->priority = 23;
        $locationCreate->hidden = true;
        $locationCreate->remoteId = '0123456789abcdef0123456789abcdef';
        $locationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // Load content type
        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'forum' );

        // Configure new content object
        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-US' );

        $contentCreate->setField( 'name', 'An awesome forum' );
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        // $sectionId is the ID of section 1
        $contentCreate->sectionId = $sectionId;
        $contentCreate->alwaysAvailable = true;

        // Create a draft
        $draft = $contentService->createContent( $contentCreate, array( $locationCreate ) );
        /* END: Inline */

        return $draft;
    }

    /**
     * Creates a fresh clean published content instance.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createContentVersion1()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Inline */
        $draft = $this->createContentDraftVersion1();

        // Publish this draft
        $content = $contentService->publishVersion( $draft->getVersionInfo() );
        /* END: Inline */

        return $content;
    }

    /**
     * Creates a new content draft named <b>$draftVersion2</b> from a currently
     * published content object.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createContentDraftVersion2()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Inline */
        $content = $this->createContentVersion1();

        // Create a new draft from the published content
        $draftVersion2 = $contentService->createContentDraft( $content->contentInfo );
        /* END: Inline */

        return $draftVersion2;
    }

    /**
     * Creates an updated content draft named <b>$draftVersion2</b> from
     * a currently published content object.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createUpdatedDraftVersion2()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Inline */
        $draftVersion2 = $this->createContentDraftVersion2();

        // Create an update struct and modify some fields
        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->initialLanguageCode = 'eng-US';
        $contentUpdate->setField( 'name', 'An awesome forum²' );
        $contentUpdate->setField( 'name', 'An awesome forum²³', 'eng-GB' );

        // Update the content draft
        $draftVersion2 = $contentService->updateContent(
            $draftVersion2->getVersionInfo(),
            $contentUpdate
        );
        /* END: Inline */

        return $draftVersion2;
    }

    /**
     * Creates an updated content object named <b>$contentVersion2</b> from
     * a currently published content object.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createContentVersion2()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Inline */
        $draftVersion2 = $this->createUpdatedDraftVersion2();

        // Publish the updated draft
        $contentVersion2 = $contentService->publishVersion( $draftVersion2->getVersionInfo() );
        /* END: Inline */

        return $contentVersion2;
    }

    /**
     * Creates an updated content draft named <b>$draft</b>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createMultipleLanguageDraftVersion1()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Inline */
        $draft = $this->createContentDraftVersion1();

        $contentUpdate = $contentService->newContentUpdateStruct();

        $contentUpdate->initialLanguageCode = 'eng-US';

        $contentUpdate->setField( 'name', 'An awesome multi-lang forum²' );

        $contentUpdate->setField( 'name', 'An awesome multi-lang forum²³', 'eng-GB' );

        $draft = $contentService->updateContent(
            $draft->getVersionInfo(),
            $contentUpdate
        );
        /* END: Inline */

        return $draft;
    }

    /**
     * Creates a published content object with versionNo=2 named
     * <b>$contentVersion2</b>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createMultipleLanguageContentVersion2()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Inline */
        $draft = $this->createMultipleLanguageDraftVersion1();

        // Publish this version.
        $contentVersion1 = $contentService->publishVersion(
            $draft->getVersionInfo()
        );

        // Create a new draft and update with same values
        $draftVersion2 = $contentService->createContentDraft(
            $contentVersion1->contentInfo
        );

        $contentUpdate = $contentService->newContentUpdateStruct();
        foreach ( $draftVersion2->getFields() as $field )
        {
            $contentUpdate->setField( $field->fieldDefIdentifier, $field->value, $field->languageCode );
        }

        $contentService->updateContent(
            $draftVersion2->getVersionInfo(),
            $contentUpdate
        );

        // Finally publish version 2
        $contentVersion2 = $contentService->publishVersion(
            $draftVersion2->getVersionInfo()
        );
        /* END: Inline */

        return $contentVersion2;
    }
}
