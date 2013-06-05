<?php
/**
 * File containing the BaseContentTypeServiceTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Base class for content type specific tests.
 */
abstract class BaseContentTypeServiceTest extends BaseTest
{
    /**
     * Creates a fully functional ContentTypeDraft and returns it.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    protected function createContentTypeDraft()
    {
        $repository = $this->getRepository();

        $creatorId = $this->generateId( 'user', 14 );
        /* BEGIN: Inline */
        $contentTypeService = $repository->getContentTypeService();

        $groups = array(
            $contentTypeService->loadContentTypeGroupByIdentifier( 'Content' ),
            $contentTypeService->loadContentTypeGroupByIdentifier( 'Setup' )
        );

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'blog-post' );
        $typeCreate->mainLanguageCode = 'eng-US';
        $typeCreate->remoteId = '384b94a1bd6bc06826410e284dd9684887bf56fc';
        $typeCreate->urlAliasSchema = 'url|scheme';
        $typeCreate->nameSchema = 'name|scheme';
        $typeCreate->names = array(
            'eng-US' => 'Blog post',
            'ger-DE' => 'Blog-Eintrag',
        );
        $typeCreate->descriptions = array(
            'eng-US' => 'A blog post',
            'ger-DE' => 'Ein Blog-Eintrag',
        );
        // $creatorId contains the ID of user 23
        $typeCreate->creatorId = $creatorId;
        $typeCreate->creationDate = $this->createDateTime();

        $titleFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'ezstring'
        );
        $titleFieldCreate->names = array(
            'eng-US' => 'Title',
            'ger-DE' => 'Titel',
        );
        $titleFieldCreate->descriptions = array(
            'eng-US' => 'Title of the blog post',
            'ger-DE' => 'Titel des Blog-Eintrages',
        );
        $titleFieldCreate->fieldGroup = 'blog-content';
        $titleFieldCreate->position = 1;
        $titleFieldCreate->isTranslatable = true;
        $titleFieldCreate->isRequired = true;
        $titleFieldCreate->isInfoCollector = false;
        $titleFieldCreate->validatorConfiguration = array(
            'StringLengthValidator' => array(
                'minStringLength' => 0,
                'maxStringLength' => 0,
            ),
        );
        $titleFieldCreate->fieldSettings = array();
        $titleFieldCreate->isSearchable = true;

        $typeCreate->addFieldDefinition( $titleFieldCreate );

        $bodyFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'body', 'eztext'
        );
        $bodyFieldCreate->names = array(
            'eng-US' => 'Body',
            'ger-DE' => 'Textkörper',
        );
        $bodyFieldCreate->descriptions = array(
            'eng-US' => 'Body of the blog post',
            'ger-DE' => 'Textkörper des Blog-Eintrages',
        );
        $bodyFieldCreate->fieldGroup = 'blog-content';
        $bodyFieldCreate->position = 2;
        $bodyFieldCreate->isTranslatable = true;
        $bodyFieldCreate->isRequired = true;
        $bodyFieldCreate->isInfoCollector = false;
        $bodyFieldCreate->validatorConfiguration = array();
        $bodyFieldCreate->fieldSettings = array(
            'textRows' => 80
        );
        $bodyFieldCreate->isSearchable = true;

        $typeCreate->addFieldDefinition( $bodyFieldCreate );

        $contentTypeDraft = $contentTypeService->createContentType(
            $typeCreate,
            $groups
        );
        /* END: Inline */

        return $contentTypeDraft;
    }

    /**
     * Creates a fresh clean content draft.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createContentDraft()
    {
        $repository = $this->getRepository();

        $parentLocationId = $this->generateId( 'location', 56 );
        $sectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Inline */
        // $parentLocationId is the id of the "/Design/eZ-publish" Location

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
        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'blog-post' );

        // Configure new content object
        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-US' );

        $contentCreate->setField( 'title', 'My awesome blog post' );
        $contentCreate->setField( 'body', 'Body is not done yet but it is going to be awesome...' );
        $contentCreate->setField( 'title', 'My marvellous blog post', 'eng-GB' );
        $contentCreate->setField( 'body', 'Body is not done yet but it is going to be jolly good...', 'eng-GB' );
        $contentCreate->remoteId = 'abcdef0123456789abcdef0123456789';
        // $sectionId is the ID of section 1
        $contentCreate->sectionId = $sectionId;
        $contentCreate->alwaysAvailable = true;

        // Create a draft
        $draft = $contentService->createContent( $contentCreate, array( $locationCreate ) );
        /* END: Inline */

        return $draft;
    }
}
