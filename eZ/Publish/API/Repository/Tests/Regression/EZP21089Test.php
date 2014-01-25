<?php
/**
 * File containing the EZP21089Test class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use DateTime;

/**
 * Test case for issue EZP-21089
 *
 * @issue EZP-21089
 */
class EZP21089Test extends BaseTest
{
    /**
     * @var \eZ\Publish\Core\Repository\DomainLogic\Values\ContentType\ContentType
     */
    private $contentType;

    protected function setUp()
    {
        parent::setUp();

        $repository = $this->getRepository();

        $contentTypeService = $repository->getContentTypeService();

        $creatorId = $repository->getCurrentUser()->id;
        $creationDate = new DateTime;

        $typeCreateStruct = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );
        $typeCreateStruct->names = array(
            'eng-GB' => 'title'
        );
        $typeCreateStruct->descriptions = array(
            'eng-GB' => 'description'
        );
        $typeCreateStruct->remoteId = "new-remoteid";
        $typeCreateStruct->creatorId = $creatorId;
        $typeCreateStruct->creationDate = $creationDate;
        $typeCreateStruct->mainLanguageCode = 'eng-GB';
        $typeCreateStruct->nameSchema = "<title>";
        $typeCreateStruct->urlAliasSchema = "<title>";

        $titleFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title',
            'ezstring'
        );
        $titleFieldCreate->names = array(
            'eng-GB' => 'title',
        );
        $titleFieldCreate->descriptions = array(
            'eng-GB' => 'title description',
        );
        $titleFieldCreate->fieldGroup = 'blog-content';
        $titleFieldCreate->position = 1;
        $titleFieldCreate->isTranslatable = true;
        $titleFieldCreate->isRequired = true;
        $titleFieldCreate->isInfoCollector = false;
        $titleFieldCreate->isSearchable = true;
        $titleFieldCreate->defaultValue = "New text line";
        $typeCreateStruct->addFieldDefinition( $titleFieldCreate );

        $objectRelationFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'body',
            'ezobjectrelation'
        );
        $objectRelationFieldCreate->names = array(
            'eng-GB' => 'object relation',
        );
        $objectRelationFieldCreate->descriptions = array(
            'eng-GB' => 'object relation description',
        );
        $objectRelationFieldCreate->fieldGroup = 'blog-content';
        $objectRelationFieldCreate->position = 2;
        $objectRelationFieldCreate->isTranslatable = false;
        $objectRelationFieldCreate->isRequired = false;
        $objectRelationFieldCreate->isInfoCollector = false;
        $objectRelationFieldCreate->isSearchable = false;
        $objectRelationFieldCreate->defaultValue = "";
        $typeCreateStruct->addFieldDefinition( $objectRelationFieldCreate );

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'first-group'
        );
        $groupCreate->creatorId = $creatorId;
        $groupCreate->creationDate = $creationDate;

        $type = $contentTypeService->createContentType(
            $typeCreateStruct,
            array( $contentTypeService->createContentTypeGroup( $groupCreate ) )
        );

        $contentTypeService->publishContentTypeDraft( $type );

        $this->contentType = $contentTypeService->loadContentType( $type->id );
    }

    public function testCreateContent()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $contentCreateStruct = $contentService->newContentCreateStruct(
            $this->contentType,
            'eng-GB'
        );
        $contentCreateStruct->setField( 'title', 'Test' );
        $contentService->createContent(
            $contentCreateStruct, array( $repository->getLocationService()->newLocationCreateStruct( 2 ) )
        );
    }
}
