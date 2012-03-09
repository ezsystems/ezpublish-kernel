<?php
/**
 * File containing the BaseContentTypeServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Values\Content\Location;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\StringLengthValidatorStub;

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

        /* BEGIN: Inline */
        $contentTypeService = $repository->getContentTypeService();

        $groups = array(
            $contentTypeService->loadContentTypeGroupByIdentifier( 'Content' ),
            $contentTypeService->loadContentTypeGroupByIdentifier( 'Setup' )
        );

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'blog-post' );
        $typeCreate->mainLanguageCode = 'eng-US';
        $typeCreate->remoteId         = '384b94a1bd6bc06826410e284dd9684887bf56fc';
        $typeCreate->urlAliasSchema   = 'url|scheme';
        $typeCreate->nameSchema       = 'name|scheme';
        $typeCreate->names = array(
            'eng-US' => 'Blog post',
            'de-DE'  => 'Blog-Eintrag',
        );
        $typeCreate->descriptions = array(
            'eng-US' => 'A blog post',
            'de-DE'  => 'Ein Blog-Eintrag',
        );
        $typeCreate->creatorId    = 23;
        $typeCreate->creationDate = new \DateTime();

        $titleFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );
        $titleFieldCreate->names = array(
            'eng-US' => 'Title',
            'de-DE'  => 'Titel',
        );
        $titleFieldCreate->descriptions = array(
            'eng-US' => 'Title of the blog post',
            'de-DE'  => 'Titel des Blog-Eintrages',
        );
        $titleFieldCreate->fieldGroup      = 'blog-content';
        $titleFieldCreate->position        = 1;
        $titleFieldCreate->isTranslatable  = true;
        $titleFieldCreate->isRequired      = true;
        $titleFieldCreate->isInfoCollector = false;
        $titleFieldCreate->validators      = array(
            new StringLengthValidatorStub(),
        );
        $titleFieldCreate->fieldSettings = array(
            'textblockheight' => 10
        );
        $titleFieldCreate->isSearchable = true;

        $typeCreate->addFieldDefinition( $titleFieldCreate );

        $bodyFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'body', 'text'
        );
        $bodyFieldCreate->names = array(
            'eng-US' => 'Body',
            'de-DE'  => 'Textkörper',
        );
        $bodyFieldCreate->descriptions = array(
            'eng-US' => 'Body of the blog post',
            'de-DE'  => 'Textkörper des Blog-Eintrages',
        );
        $bodyFieldCreate->fieldGroup      = 'blog-content';
        $bodyFieldCreate->position        = 2;
        $bodyFieldCreate->isTranslatable  = true;
        $bodyFieldCreate->isRequired      = true;
        $bodyFieldCreate->isInfoCollector = false;
        $bodyFieldCreate->validators      = array(
            new StringLengthValidatorStub(),
        );
        $bodyFieldCreate->fieldSettings = array(
            'textblockheight' => 80
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
}