<?php
/**
 * assumed as injected
 * @var eZ\Publish\API\Repository\Repository $repository
 */
$repository = null;

$contentTypeService = $repository->getContentTypeService();

// create a new content type group

// make a new instance of a ContentTypeGroupCreate class
$contentTypeGroupCreate = $contentTypeService->newContentTypeGroupCreateStruct( "newgroup" )
;
// create the group
// 5.x only - add human readable names and descriptions in multi languages
$contentTypeGroupCreate->setName( "NameOfMyNewContentTypeGroup", "eng-US" );
$contentTypeGroupCreate->setName( "NameMeinerContentTypeGruppe", "ger-DE" );
$contentTypeGroupCreate->setDescription( "This is the description of my new content type group", "eng-US" );
$contentTypeGroupCreate->setDescription( "Das ist die Bescreibung meiner neuen Content Typ Gruppe", "ger-DE" );
$contentTypeGroupCreate->initialLanguageCode = "eng-US";

// create the content type group in the repository
$contentTypeGroup = $contentTypeService->createContentTypeGroup( $contentTypeGroupCreate );

// create a new content type
// make a new instance of a ContentTypeCreate class
$contentTypeCreate =  $contentTypeService->newContentTypeCreateStruct( "newtype" );

$contentTypeCreate->remoteId = "myRemoteId-1234567890";
$contentTypeCreate->defaultAlwaysAvailable = true;

$contentTypeCreate->setName( "englishNameOfMyNewContentType", "eng-US" );
$contentTypeCreate->setDescription( "this is a description of my new content type", "eng-US" );
$contentTypeCreate->setName( "deutscherNameMeinesNeuenContentTypes", "ger-DE" );
$contentTypeCreate->setDescription( "Das ist die Bescreibung meines neuen Content Typs", "ger-DE" );
$contentTypeCreate->isContainer = false;
$contentTypeCreate->nameSchema = "<name>";
$contentTypeCreate->urlAliasSchema = "<name>";
$contentTypeCreate->initialLanguageId = "eng-US";

// add field definitions
// make a new instance of a FieldDefinitionCreate class
$fieldDefinitionCreate = $contentTypeService->newFieldDefinitionCreateStruct( "ezstring", "name" );
$fieldDefinitionCreate->setDefaultValue(
    new \ezp\Content\FieldType\TextLine\Value( "New Name" )
);
$fieldDefinitionCreate->setName( "englishNameOfMyNewField", "eng-US" );
$fieldDefinitionCreate->setDescription( "this is a description of my new field", "eng-US" );
$fieldDefinitionCreate->setName( "deutscherNameMeinesNeuenFeldes", "ger-DE" );
$fieldDefinitionCreate->setDescription( "Das ist die Bescreibung meines neuen Feldes", "ger-DE" );

$fieldDefinitionCreate->isRequired = true;
$fieldDefinitionCreate->isSearchable = true;
$fieldDefinitionCreate->fieldGroup = "TestCategory";
$fieldDefinitionCreate->isTranslatable = true;
$fieldDefinitionCreate->position = 1;

// add a string length validator
$strLenValidator = new StringLengthValidator();
$strLenValidator->initializeWithConstraints(
    array( 'maxStringLength' => 20, 'minStringLength' => 4 )
);
$fieldDefinitionCreate->setValidator( $strLenValidator );

$contentTypeCreate->addFieldDefinition( $fieldDefinitionCreate );

// add second field definitions

// make a new instance of a FieldDefinitionCreate class
$fieldDefinitionCreate = $contentTypeService->newFieldDefinitionCreateStruct( "ezinteger", "number" );

$fieldDefinitionCreate->setDefaultValue(
    new \ezp\Content\FieldType\Integer\Value( 2 )
);
$fieldDefinitionCreate->setName( "englishNameOfMyNewField2", "eng-US" );
$fieldDefinitionCreate->setDescription( "this is a description of my new field 2", "eng-US" );
$fieldDefinitionCreate->setName( "deutscherNameMeinesNeuenFeldes2", "ger-DE" );
$fieldDefinitionCreate->setDescription( "Das ist die Bescreibung meines zweiten neuen Feldes", "ger-DE" );

$fieldDefinitionCreate->isRequired = false;
$fieldDefinitionCreate->isSearchable = true;
$fieldDefinitionCreate->fieldGroup = "TestCategory";
$fieldDefinitionCreate->isTranslatable = false;
$fieldDefinitionCreate->position = 2;

// add a int range validator
$intValidator = new IntegerValueValidator();
$intValidator->initializeWithConstraints(
    array( 'maxIntegerValue' => 20, 'minIntegerValue' => 0 )
);
$fieldDefinitionCreate->setValidator( $intValidator );

$contentTypeCreate->addFieldDefinition( $fieldDefinitionCreate );

// creates the content type in the given group and fields and publishes it
$contentTypeDraft = $contentTypeService->createContentType(
    $contentTypeCreate, array( $contentTypeGroup )
);

$contentTypeService->publishContentTypeDraft( $contentTypeDraft );

// update a content tyoe

// create a new draft
$contentTypeDraft = $contentTypeService->createContentTypeDraft( $contentType );

$contentTypeUpdate = $contentTypeService->newContentTypeUpdateStruct();
$contentTypeUpdate->isContainer = true;
$contentTypeService->updateContentTypeDraft( $contentTypeDraft, $contentTypeUpdate );
// after this call the content type has still status draft

// add a third fielddefinition
$fieldDefinitionCreate = $contentTypeService->newFieldDefinitionCreateStruct( "ezboolean", "checkbox" );
$fieldDefinitionCreate->setName( "englishNameOfMyNewField3", "eng-US" );
$fieldDefinitionCreate->setDescription( "this is a description of my new field 3", "eng-US" );
$fieldDefinitionCreate->setName( "deutscherNameMeinesNeuenFeldes3","ger-DE" );
$fieldDefinitionCreate->setDescription( "Das ist die Bescreibung meines dritten neuen Feldes","ger-DE" );
$fieldDefinitionCreate->isRequired = false;
$fieldDefinitionCreate->isSearchable = false;
$fieldDefinitionCreate->fieldGroup = "TestCategory";
$fieldDefinitionCreate->isTranslatable = false;
$fieldDefinitionCreate->position = 3;

// add the new field definition
$contentTypeService->addFieldDefinition( $contentTypeDraft, $fieldDefinitionCreate );
// after this call the content type has still status draft

// update an existing field
$fieldDefinition = $contentTypeDraft->getFieldDefinition( 'number' );
$fieldDefinitionUpdate = $contentTypeService->newFieldDefinitionUpdate();
$fieldDefinitionUpdate->identifier = "newIdentifier";
// replace the integer validator with new contraints
$intValidator = new IntegerValueValidator();
$intValidator->initializeWithConstraints(
    array( 'maxIntegerValue' => 100, 'minIntegerValue' => 0 )
);
$fieldDefinitionUpdate->setValidator( $intValidator );

$contentTypeService->updateFieldDefinition( $contentType, $fieldDefinition, $fieldDefinitionUpdate );

// now publish the made changes
$contentTypeService->publishContentTypeDraft( $contentType );
