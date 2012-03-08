<?php

use eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Location,
    eZ\Publish\SPI\Persistence\Content\VersionInfo;

$content = new Content();
$content->contentInfo = new ContentInfo();
$content->contentInfo->contentId = 226;
$content->contentInfo->contentTypeId = 16;
$content->contentInfo->sectionId = 1;
$content->contentInfo->ownerId = 14;
$content->contentInfo->remoteId = '95a226fb62c1533f60c16c3769bc7c6c';
$content->contentInfo->isAlwaysAvailable = true;
$content->contentInfo->modificationDate = 1313061404;
$content->contentInfo->publicationDate = 1313047907;
$content->contentInfo->currentVersionNo = 2;
$content->contentInfo->mainLanguageCode = 'eng-US';
$content->contentInfo->name = 'Something';
$content->locations = array( new Location() );
$content->fields = array();

$versionInfo = new VersionInfo();
$versionInfo->id = 675;
$versionInfo->names = array( 'eng-US' => 'Something' );
$versionInfo->versionNo = 1;
$versionInfo->modificationDate = 1313047907;
$versionInfo->creatorId = 14;
$versionInfo->creationDate = 1313047865;
$versionInfo->status = 3;
$versionInfo->contentId = 226;
$versionInfo->initialLanguageCode = 'eng-US';
$versionInfo->languageIds = array( 2 );

$field = new Field();
$field->id = 1332;
$field->fieldDefinitionId = 183;
$field->type = 'ezstring';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$content->fields[] = $field;

$field = new Field();
$field->id = 1333;
$field->fieldDefinitionId = 184;
$field->type = 'ezstring';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$content->fields[] = $field;

$field = new Field();
$field->id = 1334;
$field->fieldDefinitionId = 185;
$field->type = 'ezauthor';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$content->fields[] = $field;

$field = new Field();
$field->id = 1335;
$field->fieldDefinitionId = 186;
$field->type = 'ezxmltext';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$content->fields[] = $field;

$field = new Field();
$field->id = 1336;
$field->fieldDefinitionId = 187;
$field->type = 'ezxmltext';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$content->fields[] = $field;

$field = new Field();
$field->id = 1337;
$field->fieldDefinitionId = 188;
$field->type = 'ezboolean';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$content->fields[] = $field;

$field = new Field();
$field->id = 1338;
$field->fieldDefinitionId = 189;
$field->type = 'ezimage';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$content->fields[] = $field;

$field = new Field();
$field->id = 1339;
$field->fieldDefinitionId = 190;
$field->type = 'ezxmltext';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$content->fields[] = $field;

$field = new Field();
$field->id = 1340;
$field->fieldDefinitionId = 191;
$field->type = 'ezdatetime';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$content->fields[] = $field;

$field = new Field();
$field->id = 1341;
$field->fieldDefinitionId = 192;
$field->type = 'ezdatetime';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$content->fields[] = $field;

$field = new Field();
$field->id = 1342;
$field->fieldDefinitionId = 193;
$field->type = 'ezkeyword';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$content->fields[] = $field;

$field = new Field();
$field->id = 1343;
$field->fieldDefinitionId = 194;
$field->type = 'ezsrrating';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$content->fields[] = $field;

$content->versionInfo = $versionInfo;

return $content;
