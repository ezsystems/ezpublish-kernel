<?php

use ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
$contentService = $sc->getRepository()->getContentService();
$content = $contentService->load( 2 );

$localeFR = ezp\Base\Locale::get( 'fre-FR' );
$localeEN = ezp\Base\Locale::get( 'eng-GB' );

try
{
    // create the translation FR and the first version in FR is based on the
    // last one in eng-GB
    $translationFR = $content->addTranslation( $localeFR, $content->translations['eng-GB']->last );
}
catch ( ezp\Base\Exception\InvalidArgumentValue $e )
{
    echo "Impossible to translate in '{$localeFR->code}', this translation already exists\n";
    exit;
}

$translationFR->last->fields['name'] = "Mon dossier";
// short cut for $translationFR->last->fields['name']->value = "Mon dossier";
// $translationFR->last is the last version added in a Translation
// others fields remain untouched, they still contain what was in the eng-GB
// version when addTranslation() was called.

$contentService->update( $content );

$versionFR = $translationFR->last;

echo "'{$versionFR->fields['name']}' ({$versionFR->locale->code})\n";

?>
