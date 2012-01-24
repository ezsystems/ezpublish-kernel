<?php
/**
 * in 5.x it is possible to add multiple translations in one version
 * the information which versions and language are translated can be put into the repository
 * via a translation info. This approach assumes that the actual translation process is done
 * via a workflow engine. By storing the source version and source language it is possible
 * to trigger new translation workflows if fields in the source language have changed.
 */

/**
 * assumed as injected
 * @var ezp\PublicAPI\Interfaces\Repository $repository
 */
$repository = null;

$contentService = $repository->getContentService();

// load the source version info of a content object
$versionInfo = $contentService->loadVersionInfoById( $contentId );

// create a draft from the before published content
$draft = $contentService->createDraftFromContent( $versionInfo->Content, $versionInfo );

/**
 * Translate one language
 */
$translationInfo = $contentService->newTranslationInfo();
$translationInfo->sourceLanguage = 'eng-US';
$translationInfo->sourceVersion = $versionInfo;
$translationInfo->destinationLanguage = 'ger-DE';
$translationInfo->destinationVersion = $draft;

$translation = $contentService->newTranslation( $translationInfo );
$translation->fields['title'] = 'Titel';
// .....

$draft = $contentService->translateVersion( $translation );

// publish the version
$newPublishedVersion = $contentService->publishDraft( $draft->versionInfo );


/**
 * Translate more than one language at once (low level)
 */



$versionUpdate = $contentService->newVersionUpdateStruct();
$versionUpdate->fields['title']['ger-DE'] = 'Titel';
// ...
$versionUpdate->fields['title']['fra-FR'] = 'Titre';
// ..

$draft = $contentService->updateVersion( $draft, $versionUpdate );

$newPublishedVersion = $contentService->publishDraft( $draft->versionInfo );

$translationInfo = $contentService->newTranslationInfo();
$translationInfo->sourceLanguage = 'eng-US';
$translationInfo->sourceVersion = $versionInfo;
$translationInfo->destinationLanguage = 'ger-DE';
$translationInfo->destinationVersion = $newPublishedVersion;

$contentService->addTranslationInfo( $translationInfo );

$translationInfo = $contentService->newTranslationInfo();
$translationInfo->sourceLanguage = 'eng-US';
$translationInfo->sourceVersion = $versionInfo;
$translationInfo->destinationLanguage = 'fra-FR';
$translationInfo->destinationVersion = $newPublishedVersion;

$contentService->addTranslationInfo( $translationInfo );
