<?php
/**
 * in 5.x it is possible to add multiple translations in one version
 * the information which versions and language are translated can be put into the repository
 * via a translation info. THis approach assumes that the acual translation process is done
 * via a workflow engine. By storing the source version and source language it is possible
 * to trigger new translation workflows if fields in the source language have changed.
 * 
 */

// load the source version info of a content object
$versionInfo = $contentService->loadVersionInfoById($CONTENT_ID);
// create a draft from the before published content
$draft = $contentService->createDraftFromVersion($versionInfo->contentInfo,$versionInfo);

/**
 * Translate one language
 */
$translationInfo = new TranslationInfo;
$translationInfo->sourceLanguage = 'eng-US';
$translationInfo->sourceVersion = $versionInfo;
$translationInfo->destinationLanguage = 'ger-DE';
$translationInfo->destinationVersion = $draft;

$translation = $contentService->newTranslation($tranlationInfo);
$translation->fields['title'] = 'Titel';
// .....

$draft = $contentService->translateVersion($translation);
// publish the version
$newPublishedVersion = $contentService->publishDraft($draft->versionInfo);


/**
 * Translate more than one language at once (low level)
 */



$versionUpdate = new VersionUpdate();
$versionUpdate->fields['title']['ger-DE'] = 'Titel';
// ...
$versionUpdate->fields['title']['fra-FR'] = 'Titre';
// ..

$draft = $contentService->updateVersion($draft,$versionUpdate);

$newPublishedVersion = $contentService->publishDraft($draft->versionInfo);

$translationInfo = new TranslationInfo;
$translationInfo->sourceLanguage = 'eng-US';
$translationInfo->sourceVersion = $versionInfo;
$translationInfo->destinationLanguage = 'ger-DE';
$translationInfo->destinationVersion = $newPublishedVersion;

$contentService->addTranslationInfo($translationInfo);

$translationInfo = new TranslationInfo;
$translationInfo->sourceLanguage = 'eng-US';
$translationInfo->sourceVersion = $versionInfo;
$translationInfo->destinationLanguage = 'fra-FR';
$translationInfo->destinationVersion = $newPublishedVersion;

$contentService->addTranslationInfo($translationInfo);
