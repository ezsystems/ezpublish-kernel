<?php
use ezp\Content\Repository as ContentRepository;

$contentId = 60;
$parentLocationId = 2;
$contentService = ContentRepository::get()->getContentService();
$treeService = ContentRepository::get()->getSubtreeService();

try
{
    $content = $contentService->load( $contentId );

    /*
     * Duplicates content with native clone command
     * __clone() method will be called to reset appropriate metadata
     * like ID, creationDate, owner, ...
     */
    $newContent = clone $content;
    $parentLocation = $treeService->load( $parentLocationId );
    $content->addLocationUnder( $parentLocation );
    $contentService->create( $newContent );
}
catch ( ezp\Content\PermissionException $e )
{
    echo "Permission issue occurred: {$e->getMessage()}\n";
    exit;
}
?>
