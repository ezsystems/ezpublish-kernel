<?php

use eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Location,
    eZ\Publish\SPI\Persistence\Content\VersionInfo;

$content = new Content();
$content->contentInfo = new ContentInfo();
$content->contentInfo->id = 226;
$content->contentInfo->contentTypeId = 16;
$content->contentInfo->sectionId = 1;
$content->contentInfo->ownerId = 14;
$content->contentInfo->remoteId = '95a226fb62c1533f60c16c3769bc7c6c';
$content->contentInfo->alwaysAvailable = false;
$content->contentInfo->modificationDate = 1313061404;
$content->contentInfo->publicationDate = 1313047907;
$content->contentInfo->currentVersionNo = 2;
$content->contentInfo->isPublished = true;
$content->contentInfo->mainLanguageCode = 'eng-US';
$content->contentInfo->name = 'Something';
$content->contentInfo->mainLocationId = 228;
$content->locations = array(
    new Location(
        array(
            "id" => "228",
            "priority" => "0",
            "hidden" => "0",
            "invisible" => "0",
            "remoteId" => "ee684db77223bb1a10300d7b250232cf",
            "contentId" => "226",
            "parentId" => "77",
            "pathIdentificationString" => "solutions/something",
            "pathString" => "/1/2/77/228/",
            "modifiedSubLocation" => "1313061404",
            "mainLocationId" => "228",
            "depth" => "3",
            "sortField" => "1",
            "sortOrder" => "1"
        )
    )
);
$content->fields = array();

$versionInfo = new VersionInfo();
$versionInfo->id = 676;
$versionInfo->names = array( 'eng-US' => 'Something' );
$versionInfo->versionNo = 2;
$versionInfo->modificationDate = 1313061404;
$versionInfo->creatorId = 14;
$versionInfo->creationDate = 1313061317;
$versionInfo->status = 1;
$versionInfo->contentId = 226;
$versionInfo->initialLanguageCode = 'eng-US';
$versionInfo->languageIds = array( 2 );

$field = new Field();
$field->id = 4000;
$field->fieldDefinitionId = 194;
$field->type = 'ezsrrating';
$field->value = new FieldValue();
$field->languageCode = 'eng-GB';
$field->versionNo = 2;

$content->fields[] = $field;

$field = new Field();
$field->id = 1332;
$field->fieldDefinitionId = 183;
$field->type = 'ezstring';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 2;

$content->fields[] = $field;

$field = new Field();
$field->id = 1333;
$field->fieldDefinitionId = 184;
$field->type = 'ezstring';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 2;

$content->fields[] = $field;

$field = new Field();
$field->id = 1334;
$field->fieldDefinitionId = 185;
$field->type = 'ezauthor';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 2;

$content->fields[] = $field;

$field = new Field();
$field->id = 1335;
$field->fieldDefinitionId = 186;
$field->type = 'ezxmltext';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 2;

$content->fields[] = $field;

$field = new Field();
$field->id = 1336;
$field->fieldDefinitionId = 187;
$field->type = 'ezxmltext';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 2;

$content->fields[] = $field;

$field = new Field();
$field->id = 1337;
$field->fieldDefinitionId = 188;
$field->type = 'ezboolean';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 2;

$content->fields[] = $field;

$field = new Field();
$field->id = 1338;
$field->fieldDefinitionId = 189;
$field->type = 'ezimage';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 2;

$content->fields[] = $field;

$field = new Field();
$field->id = 1339;
$field->fieldDefinitionId = 190;
$field->type = 'ezxmltext';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 2;

$content->fields[] = $field;

$field = new Field();
$field->id = 1340;
$field->fieldDefinitionId = 191;
$field->type = 'ezdatetime';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 2;

$content->fields[] = $field;

$field = new Field();
$field->id = 1341;
$field->fieldDefinitionId = 192;
$field->type = 'ezdatetime';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 2;

$content->fields[] = $field;

$field = new Field();
$field->id = 1342;
$field->fieldDefinitionId = 193;
$field->type = 'ezkeyword';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 2;

$content->fields[] = $field;

$field = new Field();
$field->id = 1343;
$field->fieldDefinitionId = 194;
$field->type = 'ezsrrating';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 2;

$content->fields[] = $field;

$content->versionInfo = $versionInfo;

return $content;
