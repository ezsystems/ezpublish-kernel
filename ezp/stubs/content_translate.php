<?php

use ezp\content;


$contentService = \ezp\base\Repository::get()->getContentService();
$content = $contentService->load( 2 );

$localeFR = \ezp\base\Locale::get( 'fre-FR' );
$localeEN = \ezp\base\Locale::get( 'eng-GB' );

try
{
    $translationFR = $content->addTranslation( $localeFR, $localeEN );
}
catch( \InvalidArgumentException $e )
{
    echo "Impossible to translate from '{$localeEN->code}', this translation does not exist\n";
    exit;
}

$translationFR->fields['name'] = "Mon dossier";
// short cut for $translationFR->last->fields['name']->value = "Mon dossier";
// $translationFR->last is the last version in the translation
// others fields remain untouched, they still contain what was in the eng-GB 
// version when addTranslation() was called.

$contentService->update( $content );

$versionFR = $translationFR->last;

echo "'{$versionFR->fields['name']}' ({$versionFR->locale->code})\n";

?>
