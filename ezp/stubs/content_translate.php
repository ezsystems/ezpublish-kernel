<?php

use ezp\content;


$contentService = \ezp\base\Repository::get()->getContentService();
$translationService = \ezp\base\Repository::get()->getTranslationService();
$content = $contentService->load( 2 );

$localeFR = \ezp\base\Locale::get( 'fre-FR' );
$localeEN = \ezp\base\Locale::get( 'eng-GB' );


$translationFR = $translationService->add( $content, $localeFR, $localeEN );

$translationFR->fields['name'] = "Mon dossier";
// short cut for $translationFR->last->fields['name']->value = "Mon dossier";
// $translationFR->last is the last version in the translation

$contentService->update( $content );

$versionFR = $translationFR->last;

echo "'{$versionFR->fields['name']}' ({$versionFR->locale->code})";
echo "based on {$versionFR->baseVersion->fields['name']} ({$versionFR->baseVersion->locale->code})\n";

?>
