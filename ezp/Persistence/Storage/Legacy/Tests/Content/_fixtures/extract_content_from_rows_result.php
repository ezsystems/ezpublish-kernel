<?php

use ezp\Persistence\Content,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\Location,
    ezp\Persistence\Content\Version;

$content = new Content();
$content->id = 226;
$content->name = 'Test blog title - edited';
$content->typeId = 20;
$content->sectionId = 1;
$content->ownerId = 14;
$content->remoteId = '0d5109156ee806da8e86c8cea8004434';
$content->locations = array( new Location() );
$content->alwaysAvailable = true;

$version = new Version();
$version->id = 679;
$version->versionNo = 4;
$version->modified = 1312373715;
$version->creatorId = 14;
$version->created = 1312373690;
$version->state = 1;
$version->contentId = 226;
$version->fields = array();

$versionUkTitleField = new Field();
$versionUkTitleField->id = 1338;
$versionUkTitleField->fieldDefinitionId = 214;
$versionUkTitleField->type = 'ezstring';
$versionUkTitleField->value = new FieldValue();
$versionUkTitleField->language = 'eng-GB';
$versionUkTitleField->versionNo = 4;

$version->fields[] = $versionUkTitleField;

$versionUkBodyField = new Field();
$versionUkBodyField->id = 1339;
$versionUkBodyField->fieldDefinitionId = 215;
$versionUkBodyField->type = 'ezxmltext';
$versionUkBodyField->value = new FieldValue();
$versionUkBodyField->language = 'eng-GB';
$versionUkBodyField->versionNo = 4;

$version->fields[] = $versionUkBodyField;

$versionUkPubDateField = new Field();
$versionUkPubDateField->id = 1340;
$versionUkPubDateField->fieldDefinitionId = 216;
$versionUkPubDateField->type = 'ezdatetime';
$versionUkPubDateField->value = new FieldValue();
$versionUkPubDateField->language = 'eng-GB';
$versionUkPubDateField->versionNo = 4;

$version->fields[] = $versionUkPubDateField;

$versionUsTitleField = new Field();
$versionUsTitleField->id = 1332;
$versionUsTitleField->fieldDefinitionId = 214;
$versionUsTitleField->type = 'ezstring';
$versionUsTitleField->value = new FieldValue();
$versionUsTitleField->language = 'eng-US';
$versionUsTitleField->versionNo = 4;

$version->fields[] = $versionUsTitleField;

$versionUsBodyField = new Field();
$versionUsBodyField->id = 1333;
$versionUsBodyField->fieldDefinitionId = 215;
$versionUsBodyField->type = 'ezxmltext';
$versionUsBodyField->value = new FieldValue();
$versionUsBodyField->language = 'eng-US';
$versionUsBodyField->versionNo = 4;

$version->fields[] = $versionUsBodyField;

$versionUsPubDateField = new Field();
$versionUsPubDateField->id = 1334;
$versionUsPubDateField->fieldDefinitionId = 216;
$versionUsPubDateField->type = 'ezdatetime';
$versionUsPubDateField->value = new FieldValue();
$versionUsPubDateField->language = 'eng-US';
$versionUsPubDateField->versionNo = 4;

// @FIXME: This field is not translateable and should therefore only
// occur once!
$version->fields[] = $versionUsPubDateField;

$content->version = $version;

return $content;
