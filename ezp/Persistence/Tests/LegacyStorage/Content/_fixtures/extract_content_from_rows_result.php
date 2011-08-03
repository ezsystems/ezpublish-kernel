<?php

use ezp\Persistence\Content,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\Version;

$content               = new Content();
$content->id           = 226;
$content->name         = 'Test blog title - edited';
$content->typeId       = 20;
$content->sectionId    = 1;
$content->ownerId      = 14;
$content->versionInfos = array();

$v1            = new Version();
$v1->id        = 677;
$v1->versionNo = 3;
$v1->modified  = 1312302914;
$v1->creatorId = 14;
$v1->created   = 1312302870;
$v1->state     = 3;
$v1->contentId = 226;
$v1->fields    = array();

$v1TitleField                    = new Field();
$v1TitleField->id                = 1332;
$v1TitleField->fieldDefinitionId = 214;
$v1TitleField->type              = 'ezstring';
$v1TitleField->value             = new FieldValue();
$v1TitleField->language          = 'eng-US';
$v1TitleField->versionNo         = 3;

$v1->fields[] = $v1TitleField;

$v1BodyField                    = new Field();
$v1BodyField->id                = 1333;
$v1BodyField->fieldDefinitionId = 215;
$v1BodyField->type              = 'ezxmltext';
$v1BodyField->value             = new FieldValue();
$v1BodyField->language          = 'eng-US';
$v1BodyField->versionNo         = 3;

$v1->fields[] = $v1BodyField;

$v1PubDateField                    = new Field();
$v1PubDateField->id                = 1334;
$v1PubDateField->fieldDefinitionId = 216;
$v1PubDateField->type              = 'ezdatetime';
$v1PubDateField->value             = new FieldValue();
$v1PubDateField->language          = 'eng-US';
$v1PubDateField->versionNo         = 3;

$v1->fields[] = $v1PubDateField;

$content->versionInfos[] = $v1;

$v2            = new Version();
$v2->id        = 679;
$v2->versionNo = 4;
$v2->modified  = 1312373715;
$v2->creatorId = 14;
$v2->created   = 1312373690;
$v2->state     = 1;
$v2->contentId = 226;
$v2->fields    = array();

$v2UkTitleField                    = new Field();
$v2UkTitleField->id                = 1338;
$v2UkTitleField->fieldDefinitionId = 214;
$v2UkTitleField->type              = 'ezstring';
$v2UkTitleField->value             = new FieldValue();
$v2UkTitleField->language          = 'eng-GB';
$v2UkTitleField->versionNo         = 4;

$v2->fields[] = $v2UkTitleField;

$v2UkBodyField                    = new Field();
$v2UkBodyField->id                = 1339;
$v2UkBodyField->fieldDefinitionId = 215;
$v2UkBodyField->type              = 'ezxmltext';
$v2UkBodyField->value             = new FieldValue();
$v2UkBodyField->language          = 'eng-GB';
$v2UkBodyField->versionNo         = 4;

$v2->fields[] = $v2UkBodyField;

$v2UkPubDateField                    = new Field();
$v2UkPubDateField->id                = 1340;
$v2UkPubDateField->fieldDefinitionId = 216;
$v2UkPubDateField->type              = 'ezdatetime';
$v2UkPubDateField->value             = new FieldValue();
$v2UkPubDateField->language          = 'eng-GB';
$v2UkPubDateField->versionNo         = 4;

$v2->fields[] = $v2UkPubDateField;

$v2UsTitleField                    = new Field();
$v2UsTitleField->id                = 1332;
$v2UsTitleField->fieldDefinitionId = 214;
$v2UsTitleField->type              = 'ezstring';
$v2UsTitleField->value             = new FieldValue();
$v2UsTitleField->language          = 'eng-US';
$v2UsTitleField->versionNo         = 4;

$v2->fields[] = $v2UsTitleField;

$v2UsBodyField                    = new Field();
$v2UsBodyField->id                = 1333;
$v2UsBodyField->fieldDefinitionId = 215;
$v2UsBodyField->type              = 'ezxmltext';
$v2UsBodyField->value             = new FieldValue();
$v2UsBodyField->language          = 'eng-US';
$v2UsBodyField->versionNo         = 4;

$v2->fields[] = $v2UsBodyField;

$v2UsPubDateField                    = new Field();
$v2UsPubDateField->id                = 1334;
$v2UsPubDateField->fieldDefinitionId = 216;
$v2UsPubDateField->type              = 'ezdatetime';
$v2UsPubDateField->value             = new FieldValue();
$v2UsPubDateField->language          = 'eng-US';
$v2UsPubDateField->versionNo         = 4;

// @FIXME: This field is not translateable and should therefore only
// occur once!
$v2->fields[] = $v2UsPubDateField;

$content->versionInfos[] = $v2;

return $content;
