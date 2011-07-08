<?php
/**
 * Delete a Content
 */
use ezp\Content\Repository as ContentRepository;

$contentService = ContentRepository::get()->getContentService();
try
{
    $content = $contentService->load( 60 );
    $contentService->delete( $content );
}
catch ( ezp\Content\ContentNotFoundException $e )
{
    echo "Content could not be found in the repository !\n";
    exit;
}
catch( ezp\Content\PermissionException $e )
{
    echo "A permission issue occurred while deleting content: {$e->getMessage()}\n";
    exit;
}
?>
