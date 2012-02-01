<?php
/**
 * @example Create a content in multiple languages at once
 */

use eZ\Publish\API\Interfaces\Repository;

/**
 * Assumed as injected
 * @var eZ\Publish\API\Interfaces\Repository
 */
$repository = new Repository;

$contentService = $repository->getContentService();
$locationService = $repository->getLocationService();
$contentTypeService = $repository->getContentTypeService();

$createStruct = $contentService->newContentCreateStruct(
    $contentTypeService->loadContentTypeByIdentifier( 'article' ),
    'eng-GB'
);
$createStruct->fields['title']['eng-GB'] = 'This is my title';
$createStruct->fields['content']['eng-GB'] = '<p>What a <strong>wonderful</strong> world !</p>';
$createStruct->fields['title']['fre-FR'] = 'Ceci est mon titre';
$createStruct->fields['content']['fre-FR'] = '<p>Quel monde <strong>merveilleux</strong> !</p>';
// Non translatable field
$createStruct->fields['show_comments'] = true;

$draft = $contentService->createContentDraft(
    $createStruct,
    array(
        $locationService->newLocationCreateStruct( 123 )
    )
);
$publishedVersion = $contentService->publishDraft( $draft->versionInfo );
