<?php

use ezp\Base\ServiceContainer as Container;

$sc = new Container();
$contentService = $sc->getRepository()->getContentService();
$content = $contentService->load( 2 );

/**
 * assumes that $content has 2 translations, one in eng-GB (main) and one in
 * fre-FR
 */

$localeFR = ezp\Base\Locale::get( 'fre-FR' );

try
{
    $content->removeTranslation( $localeFR );
}
catch ( ezp\Base\Exception\InvalidArgumentValue $e )
{
    echo "Impossible to remove translationg '{$localeFR->code}': {$e->getMessage()}\n";
    exit;
}

$contentService->update( $content );

?>
