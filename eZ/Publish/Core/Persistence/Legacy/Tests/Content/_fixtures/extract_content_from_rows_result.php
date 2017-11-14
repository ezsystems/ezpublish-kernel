<?php

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

$content = new Content();

$content->fields = array();

$versionInfo = new VersionInfo();
$versionInfo->id = 676;
$versionInfo->names = array( 'eng-US' => 'Something', 'eng-GB' => 'Something' );
$versionInfo->versionNo = 2;
$versionInfo->modificationDate = 1313061404;
$versionInfo->creatorId = 14;
$versionInfo->creationDate = 1313061317;
$versionInfo->status = 1;
$versionInfo->initialLanguageCode = 'eng-US';
$versionInfo->languageCodes = ['eng-US'];

$versionInfo->contentInfo = new ContentInfo();
$versionInfo->contentInfo->id = 226;
$versionInfo->contentInfo->contentTypeId = 16;
$versionInfo->contentInfo->sectionId = 1;
$versionInfo->contentInfo->ownerId = 14;
$versionInfo->contentInfo->remoteId = '95a226fb62c1533f60c16c3769bc7c6c';
$versionInfo->contentInfo->alwaysAvailable = false;
$versionInfo->contentInfo->modificationDate = 1313061404;
$versionInfo->contentInfo->publicationDate = 1313047907;
$versionInfo->contentInfo->currentVersionNo = 2;
$versionInfo->contentInfo->isPublished = true;
$versionInfo->contentInfo->mainLanguageCode = 'eng-US';
$versionInfo->contentInfo->name = 'Something';
$versionInfo->contentInfo->mainLocationId = 228;

$content->versionInfo = $versionInfo;

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
$field->type = 'ezrichtext';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 2;

$content->fields[] = $field;

$field = new Field();
$field->id = 1336;
$field->fieldDefinitionId = 187;
$field->type = 'ezrichtext';
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
$field->type = 'ezrichtext';
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

return $content;
