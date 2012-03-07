<?php

use eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Location,
    eZ\Publish\SPI\Persistence\Content\Version;

$content = new Content();
$content->id = 226;
$content->typeId = 16;
$content->sectionId = 1;
$content->ownerId = 14;
$content->remoteId = '95a226fb62c1533f60c16c3769bc7c6c';
$content->locations = array( new Location() );
$content->alwaysAvailable = true;
$content->modified = 1313061404;
$content->published = 1313047907;
$content->currentVersionNo = 2;
$content->initialLanguageId = 2;
$content->status = 1;

$version = new Version();
$version->id = 675;
$version->name = array( 'eng-US' => 'Something' );
$version->versionNo = 1;
$version->modified = 1313047907;
$version->creatorId = 14;
$version->created = 1313047865;
$version->status = 3;
$version->contentId = 226;
$version->initialLanguageId = 2;
$version->languageIds = array( 2 );
$version->fields = array();

$field = new Field();
$field->id = 1332;
$field->fieldDefinitionId = 183;
$field->type = 'ezstring';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$version->fields[] = $field;

$field = new Field();
$field->id = 1333;
$field->fieldDefinitionId = 184;
$field->type = 'ezstring';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$version->fields[] = $field;

$field = new Field();
$field->id = 1334;
$field->fieldDefinitionId = 185;
$field->type = 'ezauthor';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$version->fields[] = $field;

$field = new Field();
$field->id = 1335;
$field->fieldDefinitionId = 186;
$field->type = 'ezxmltext';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$version->fields[] = $field;

$field = new Field();
$field->id = 1336;
$field->fieldDefinitionId = 187;
$field->type = 'ezxmltext';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$version->fields[] = $field;

$field = new Field();
$field->id = 1337;
$field->fieldDefinitionId = 188;
$field->type = 'ezboolean';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$version->fields[] = $field;

$field = new Field();
$field->id = 1338;
$field->fieldDefinitionId = 189;
$field->type = 'ezimage';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$version->fields[] = $field;

$field = new Field();
$field->id = 1339;
$field->fieldDefinitionId = 190;
$field->type = 'ezxmltext';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$version->fields[] = $field;

$field = new Field();
$field->id = 1340;
$field->fieldDefinitionId = 191;
$field->type = 'ezdatetime';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$version->fields[] = $field;

$field = new Field();
$field->id = 1341;
$field->fieldDefinitionId = 192;
$field->type = 'ezdatetime';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$version->fields[] = $field;

$field = new Field();
$field->id = 1342;
$field->fieldDefinitionId = 193;
$field->type = 'ezkeyword';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$version->fields[] = $field;

$field = new Field();
$field->id = 1343;
$field->fieldDefinitionId = 194;
$field->type = 'ezsrrating';
$field->value = new FieldValue();
$field->languageCode = 'eng-US';
$field->versionNo = 1;

$version->fields[] = $field;

$content->version = $version;

return $content;
