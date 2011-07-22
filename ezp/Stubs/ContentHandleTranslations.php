<?php
/**
 * Assume that:
 * $content has been translated in eng-GB, fre-FR and nor-NO but no published
 * version exists in nor-NO
 */

use ezp\Content, ezp\Base\ServiceContainer;

$sc = new ServiceContainer();
$contentService = $sc->getRepository()->getContentService();
$content = $contentService->load( 2 );

// fields of each published version in the given locale
$fieldsFR = $content->translations['fre-FR']->fields;
$fieldsEN = $content->translations['eng-GB']->fields;

echo "Name fre-FR : {$fieldsFR['name']->value}\n";
echo "Name eng-GB : {$fieldsGB['name']->value}\n";

try
{
    $fieldsNO = $content->translations['nor-NO']->fields;
}
catch ( ezp\Base\Exception\NotFound $e )
{
    echo "No published translation in nor-NO, but we can deal with the last version in this translation\n";
    $fieldsNO = $content->translations['nor-NO']->last->fields;
    echo "Name nor-NO (not yet published): {$fieldsNO['name']->value}\n";
}

?>
