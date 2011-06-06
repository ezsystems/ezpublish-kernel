<?php
/**
 * Move a content to trash
 */
use ezp\Content\Repository as ContentRepository;

$contentService = ContentRepository::getContentService();
$content = $contentService->load( 60 );

echo "Now Trashing content\n";
$trashService = ContentRepository::getTrashService();
$trashService->trash( $content );
echo "Now Content is no longer publicly available\n";

echo "Restoring content from trash\n";
$trashService->untrash( $content );

?>
