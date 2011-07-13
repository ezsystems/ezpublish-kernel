<?php
/**
 * Move a content to trash
 */
use ezp\Base\ServiceContainer;

$sc = new ServiceContainer();
$contentService = $sc->getRepository()->getContentService();
$content = $contentService->load( 60 );

echo "Now Trashing content\n";
$contentService->trash( $content );
echo "Now Content is no longer publicly available\n";

echo "Restoring content from trash\n";
$contentService->untrash( $content );

?>
