<?php
/**
 * Assume that:
 * $content has been translated in eng-GB, fre-FR and nor-NO but no published
 * version exists in nor-NO
 */

use ezp\Content, ezp\Base\Service\Container;

$sc = new Container();
$contentService = $sc->getRepository()->getContentService();
$content = $contentService->load( 2 );

// fields of each published version in the given locale
$fieldsFR = $content->translations['fre-FR']->getFields();
$fieldsEN = $content->translations['eng-GB']->getFields();

echo "Name fre-FR : {$fieldsFR['name']->value}\n";
echo "Name eng-GB : {$fieldsGB['name']->value}\n";

try
{
    $fieldsNO = $content->translations['nor-NO']->getFields();
}
catch ( ezp\Base\Exception\NotFound $e )
{
    echo "No published translation in nor-NO, but we can deal with the last version in this translation\n";
    $fieldsNO = $content->translations['nor-NO']->last->getFields();
    echo "Name nor-NO (not yet published): {$fieldsNO['name']->value}\n";
}

?>
